<?php
/**
 * Localise wp install
 */
namespace Console;

use Console\Util\Env;
use Exception;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
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
            ->addOption('configFile', null, InputArgument::OPTIONAL, '.butler.env file path (default to current folder)', './.butler.env');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // pull the latest code from repository
        // $output->writeln('<info>Getting the latest code from git remote (origin/master)</info>');
        // $process = new Process(['git', 'pull', 'origin', 'master']);
        // $process->run(function ($type, $buffer) {
        //     echo $buffer;
        // });
        // check if there's .env file
        $configFile = $input->getOption('configFile');
        if (!file_exists($configFile)) {
            // run butler env to generate file
            $command = $this->getApplication()->find('env');
            $command->run(new ArrayInput(['command' => 'env']), $output);
        }

        // read env file to get config
        $config = Env::loadConfig($configFile);

        // show config
        Env::printConfig($config, $output);

        // Confirm to continue
        $helper = $this->getHelper('question');
        $confirm_config = new ConfirmationQuestion('<question>Generate local site using these config? (Y/n):</question> ', true);

        if (!$helper->ask($input, $output, $confirm_config)) {
            $output->writeln('K, bye!');
            return;
        }

        // localise wp instance
        $this->localise($config, $input, $output);

        //
    }

    public function localise($config, $input, $output)
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

        // // Create user account
        // $helper = $this->getHelper('question');
        // $output->writeln('<info>Creating new admin user account.</info>');
        // $q = new Question("Please enter the admin user email:\n", '');
        // $user_email = $helper->ask($input, $output, $q);

        // $cmd = 'wp user create ' . trim($user_email) . ' ' . trim($user_email) . ' --role=administrator';

        // $process = Process::fromShellCommandline($cmd);
        // $process->setWorkingDirectory('./');
        // $process->run(function ($type, $buffer) {
        //     echo $buffer;
        // });

        // $output->writeln('<info>Admin user created.</info>');
    }
    public function validateEnvVar(array $env)
    {
        return empty(array_diff(['domains', 'db_host', 'db_user', 'db_pass'], array_keys($env)));
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
        $db_name = $config['db_name'] ? $config['db_name'] : $config['domains']['primary'];

        $cmd = 'wp config create --dbname=' . $db_name . ' --dbuser=' . $config['db_user'] . ' --dbhost=' . $config['db_host'] . ' --prompt=dbpass < ' . $tmp_pass_file;

        $process = Process::fromShellCommandline($cmd);

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        // remove tmp password file
        unlink($tmp_pass_file);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // update FS_METHOD to direct

        $cmd = 'wp config set FS_METHOD direct';

        $process = Process::fromShellCommandline($cmd);

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return true;
    }
}
