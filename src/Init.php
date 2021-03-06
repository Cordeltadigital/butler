<?php
/**
 * run this on server, make this wp instance consumption ready.
 */
namespace Console;

use Console\Util\Env;
use Console\Util\Git;
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
        $working_dir = getcwd();
        $confirm = new ConfirmationQuestion('<question>Is this the new root folder for the site [' . $working_dir . ']? (Y/n):</question> ', true);

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

            // create folder structure
            if (!is_dir('site')) {
                mkdir('site');
            }

            // create env file
            $command = $this->getApplication()->find('env');
            $arguments = [
                'command' => 'env',
            ];

            $input = new ArrayInput($arguments);
            $command->run($input, $output);

            // copy env file into site folder so "takeover" command doesn't ask again
            copy('./.butler.env', './site/.butler.env');

            // copy scripts
            $command = $this->getApplication()->find('generate:scripts');
            $arguments = [
                'command' => 'generate:scripts',
            ];

            $input = new ArrayInput($arguments);
            $returnCode = $command->run($input, $output);
            $output->writeln($returnCode);

            chdir('site');

            $this->initWP($input, $output);

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
            chdir($working_dir);
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

        // download wp
        $output->writeln('<info>Downloading WordPress core</info>');
        $cmd = 'wp core download';

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(7200);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // localise
        $output->writeln('<info>Localising wp site</info>');
        $command = $this->getApplication()->find('takeover');

        $command->run(new ArrayInput([
            'command' => 'takeover',
        ]), $output);

        // install wordpress
        $command = $this->getApplication()->find('install');

        $command->run(new ArrayInput([
            'command' => 'install',
        ]), $output);

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
