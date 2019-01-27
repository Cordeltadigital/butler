<?php
/**
 * Localise wp install
 */
namespace Console;

use Console\Util\Env as Env;
use Console\Util\Validator as Validator;
use Exception;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Takeover extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('takeover')
            ->setDescription('Initiate wp instance locally based on file system.')
            ->addOption('envFile', null, InputArgument::OPTIONAL, '.env file path (default to current folder)', './.env');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // pull the latest code from repository
        $output->writeln('<info>Getting the latest code from git remote (origin/master)</info>');
        // $process = new Process(['git', 'pull', 'origin', 'master']);
        // $process->run(function ($type, $buffer) {
        //     echo $buffer;
        // });
        // check if there's .env file
        $envFile = $input->getOption('envFile');
        if (!file_exists($envFile)) {
            $this->create_env_file($input, $output, $envFile);
        }

        // read env file to get config
        $config = Env::loadConfig($envFile);

        // show config
        $output->writeln("============ Config =============");

        foreach ($config as $key => $value) {
            $output->writeln("$key : $value");
        }
        $output->writeln("=================================");

        // Confirm to continue
        $helper = $this->getHelper('question');
        $confirm_config = new ConfirmationQuestion('<question>Generate local site using these config? (Y/n):</question> ', true);

        if (!$helper->ask($input, $output, $confirm_config)) {
            $output->writeln('K, bye!');
            return;
        }

        // localise wp instance
        $this->localise($config, $output);

        //
    }

    public function localise($config, $output)
    {
        if (file_exists('wp-config.php')) {
            $output->writeln('<info>Attempting to generate local site with existing wp-config.php.</info>');
        } else {
            try {
                $this->createWpConfigFile($config);
            } catch (Exception $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
                return;
            }
        }

        // import Db if sql file exists

        $command = $this->getApplication()->find('db:import');

        $arguments = [
            'command' => 'db:import',
            // 'name' => 'Fabien',
            // '--yell' => true,
        ];

        $importInput = new ArrayInput($arguments);
        $returnCode = $command->run($importInput, $output);

        // create virualhost entry and hosts entry

        // show msg

    }
    public function validateEnvVar(array $env)
    {
        return empty(array_diff(['domain', 'db_host', 'db_user', 'db_pass'], array_keys($env)));
    }

    public function createWpConfigFile($config)
    {
        if (!$this->validateEnvVar($config)) {
            throw new \Exception('Config not valid.');
        }
        $pass = $config['db_pass'];

        // create tmp password file
        $tmp_pass_file = './.ps.txt';
        file_put_contents($tmp_pass_file, $pass);

        // $process = new Process(['wp', 'config', 'create', '--dbname=' . $config['domain'], '--dbuser=' . $config['db_user'], '--dbhost=' . $config['db_host'], '--dbpass=' . $pass]);

        $cmd = 'wp config create --dbname=' . $config['domain'] . ' --dbuser=' . $config['db_user'] . ' --dbhost=' . $config['db_host'] . ' --prompt=dbpass < ' . $tmp_pass_file;

        $process = Process::fromShellCommandline($cmd);
        $process->setWorkingDirectory('./');
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        // remove tmp password file
        unlink($tmp_pass_file);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return true;
    }

    /**
     * create a new .env file
     * @param [type] $input
     * @param [type] $output
     * @return void
     */
    public function create_env_file(InputInterface $input, OutputInterface $output, $envFile)
    {
        $helper = $this->getHelper('question');
        $question_start = new ConfirmationQuestion('<question>.env doesn\'t exist, create a new .env file ' . $envFile . '? (Y/n): </question>', true);

        if (!$helper->ask($input, $output, $question_start)) {
            $output->writeln('K, bye!');
            return;
        }

        // create new .env file
        $conf = [
            'domain' => '',
            'db_user' => '',
            'db_pass' => '',
            'db_host' => '',
        ];

        $output->writeln('<comment>You can change these settings in .env file after generation.</comment>');

        // ask for domain
        $q_domain = new Question('Please enter the domain of this site. (e.g. example.cordelta.digital):', 'example.cordelta.digital');

        $q_domain->setValidator(function ($answer) {
            if (!Validator::isDomainValid($answer)) {
                throw new \RuntimeException(
                    'Invalid domain, please try again.'
                );
            }

            return $answer;
        });
        $a_domain = $helper->ask($input, $output, $q_domain);

        $conf['domain'] = $a_domain;

        // ask for db credentials
        // db host
        $q = new Question('Please enter the database host (default=localhost): ', 'localhost');
        $conf['db_host'] = $this->setupEnvVar('db_host', $input, $output, $q);

        // db user
        $q = new Question('Please enter the database username: ', 'butler');
        $conf['db_user'] = $this->setupEnvVar('db_user', $input, $output, $q);

        // db pass
        $q = new Question('Please enter the password for this database user: ', '');
        $q->setHidden(true);
        $conf['db_pass'] = $this->setupEnvVar('db_pass', $input, $output, $q);

        $output->writeln('<info>Generating new .env file ' . $envFile . '...</info>');
        Env::generateEnvFile($conf, $envFile);
        $output->writeln('<info>Done.</info>');
    }

    public function setupEnvVar($key, $input, $output, $question)
    {
        $helper = $this->getHelper('question');
        $result = Env::getGlobalEnv($key);
        $create_new = empty($result);
        if ($result) {
            $q = new ConfirmationQuestion('There is a global ' . $key . ' config (' . $result . '), use it here? (Y/n): ', true);
            if (!$helper->ask($input, $output, $q)) {
                $create_new = true;
            }
        }
        if ($create_new) {
            $answer = $helper->ask($input, $output, $question);
            $result = $answer;

            $save_var = new ConfirmationQuestion('Save this config for other wp sites (Y/n): ', true);
            if ($helper->ask($input, $output, $save_var)) {
                Env::saveGlobalEnv($key, $result);
                $output->writeln('Global butler setting saved. [' . $key . ']');
            }
        }

        return $result;
    }
}
