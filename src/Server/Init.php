<?php
/**
 * run this on server, make this wp instance consumption ready.
 */
namespace Console\Server;

use Exception;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
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
        $this->setName('server:init')
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
        $confirm = new ConfirmationQuestion('<question>Do you want to initiate new Wordpress project in this folder (' . getcwd() . ')? (Y/n):</question> ', true);

        if (!$helper->ask($input, $output, $confirm)) {
            $output->writeln('K, bye!');
            return false;
        }

        // git init
        try {
            $this->initGit($input, $output);
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return;
        }

        // git add remote origin xxxxx
        try {
            $this->gitRemote($input, $output);
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return;
        }
        // install brand new wp
        // export database
        // git push
    }

    private function preInit(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Let\'s get started!</info>');

        $output->writeln('This process will not be able to create git repo for you, please make sure you have a repo created already under https://bitbucket.org/cordeltadigital/');

        $helper = $this->getHelper('question');
        $confirm = new ConfirmationQuestion('<question>Have you created the repository? (Y/n):</question> ', true);

        if (!$helper->ask($input, $output, $confirm)) {
            $output->writeln('K, bye!');
            return false;
        }
        return true;
    }

    public function initGit(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Awesome! let\'s initialise our local git.</info>');

        $cmd = 'git init';
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output->writeln('<comment>Git init... done.</comment>');
    }

    public function gitRemote(InputInterface $input, OutputInterface $output)
    {

        // @TODO: check if origin exists

        $helper = $this->getHelper('question');
        $output->writeln('<info>Let\'s link up remote git repo</info>');

        $q = new Question('Please enter the git ssh url, it looks like this: git@bitbucket.org:cordeltadigital/xxxx.git:', '');

        $git_remote = $helper->ask($input, $output, $q);

        $cmd = 'git remote add origin ' . $git_remote;
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output->writeln('<comment>Git remote added.</comment>');

        $cmd = 'git pull origin master';
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output->writeln('<comment>You have the latest code.</comment>');
    }
}
