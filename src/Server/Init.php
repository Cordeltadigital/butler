<?php
/**
 * run this on server, make this wp instance consumption ready.
 */
namespace Console\Server;

use Console\Util\Env;
use Console\Util\Git as Git;
use Exception;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Init extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('init')
            ->setDescription('Make this wp instance consumption ready.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        // check requirements
        // make sure you have a repo here https://bitbucket.org/cordeltadigital/xxxx
        if (!$this->preInit($input, $output)) {
            return;
        }

        // confirm if user wants to init project in current folder?
        $confirm = new ConfirmationQuestion('<question>Is this the new root folder for the site [' . getcwd() . ']? (Y/n):</question> ', true);

        if (!$helper->ask($input, $output, $confirm)) {
            $output->writeln('K, bye!');
            return false;
        }

        try {
            // git init
            $useExistingRepo = false;
            if (Git::isRepo()) {
                // confirm if user wants to use current git settings
                $output->writeln('Existsing git repo detected:');

                $origin = Git::getOrigin();
                $output->writeln('<info>' . $origin . '</info>');

                $confirm = new ConfirmationQuestion('<question>Do you want to use the existing git repo? (Y/n):</question> ', true);
                $useExistingRepo = $helper->ask($input, $output, $confirm);
            }

            if (!$useExistingRepo) {
                // git init
                Git::init($input, $output);

                // git add remote origin xxxxx
                Git::setRemote($input, $output, $helper);
            }

            Git::pull($input, $output);

            // if no file, install brand new wp and export database
            if (!is_dir('wp-content')) {
                $output->writeln('<info>No wordpress content detected, initiating a new site.</info>');

                $this->initWP($input, $output);
            }
            // .gitignore
            file_put_contents('.gitignore', "
.env
wp-config.php
        ", FILE_APPEND);

            // select starter template
            // $question = new ChoiceQuestion(
            //     'Please select your favorite colors (defaults to red and blue)',
            //     ['red', 'blue', 'yellow'],
            //     '0,1'
            // );
            // $question->setMultiselect(true);

            // $colors = $helper->ask($input, $output, $question);
            // $output->writeln('You have just selected: ' . implode(', ', $colors));

            // export db
            $this->exportDB($input, $output);

            // git push
            Git::addAll($input, $output);
            Git::commit($input, $output, '[Butler] site initiated.');
            Git::push($input, $output);
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return;
        }
    }

    private function preInit(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Let\'s get started!</info>');

        $output->writeln('<fg=yellow>This process will not be able to create git repo for you, please make sure you have a repo created already under https://bitbucket.org/cordeltadigital/</>');

        $helper = $this->getHelper('question');
        $confirm = new ConfirmationQuestion('<question>Have you created the repository? (Y/n):</question> ', true);

        if (!$helper->ask($input, $output, $confirm)) {
            $output->writeln('K, bye!');
            return false;
        }
        return true;
    }

    private function initWP(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Installing brand new WordPress</info>');

        // download wp
        $cmd = 'wp core download';

        $process = Process::fromShellCommandline($cmd);
        $process->setWorkingDirectory('./');
        $process->setTimeout(7200);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // localise
        $output->writeln('<info>Localising wp site.</info>');
        $command = $this->getApplication()->find('takeover');

        $command->run(new ArrayInput([
            'command' => 'takeover',
        ]), $output);

        // install wordpress
        $config = Env::loadConfig();
        $cmd = 'wp core install --url=' . $config['domain'] . ' --title=' . $config['domain'] . ' --admin_user=butler --admin_email=sean.wu@cordelta.com';

        $process = Process::fromShellCommandline($cmd);
        $process->setWorkingDirectory('./');
        $process->setTimeout(7200);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private function exportDB($input, $output)
    {
        $output->writeln('<info>Exporting database.</info>');
        $command = $this->getApplication()->find('db:export');

        $arguments = [
            'command' => 'db:export',
        ];

        $input = new ArrayInput($arguments);
        return $command->run($input, $output);
    }
}
