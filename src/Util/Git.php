<?php
namespace Console\Util;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Git
{
    public static function init(InputInterface $input, OutputInterface $output)
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

    public static function isRepo($cwd = './')
    {
        \chdir($cwd);
        return is_dir('.git');
    }

    public static function setRemote(InputInterface $input, OutputInterface $output, $helper)
    {

        $output->writeln('<info>Let\'s link up remote git repo</info>');

        $q = new Question('Please enter the git ssh url, it looks like this: git@bitbucket.org:cordeltadigital/xxxx.git:', '');

        $git_remote = $helper->ask($input, $output, $q);

        $originExists = !empty(self::getOrigin());
        if ($originExists) {
            $output->writeln('Removing existing git origin.');
            $cleanCmd = 'git remote rm origin';
            $process = Process::fromShellCommandline($cleanCmd);
            $process->run(function ($type, $buffer) {
                echo $buffer;
            });
        }

        $output->writeln('Adding new git origin.');
        $cmd = 'git remote add origin ' . $git_remote;
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output->writeln('<comment>Git remote added.</comment>');

    }
    public static function getOrigin()
    {
        $cmd = 'git remote -v';
        $process = Process::fromShellCommandline($cmd);
        $process->run();
        return $process->getOutput();
    }
    public static function getBranch()
    {
        $cmd = 'git branch -r';
        $process = Process::fromShellCommandline($cmd);
        $process->run();
        return $process->getOutput();
    }

    public static function pull($input, $output)
    {
        $output->writeln('<info>[Git] Pulling latest code...</info>');
        if (empty(self::getBranch())) {
            $output->writeln('<info>[Git] No local branch.</info>');
            return;
        }
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

    public static function addAll($input, $output)
    {
        $output->writeln('<info>[Git] Indexing files...</info>');
        $cmd = 'git add .';

        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            // echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output->writeln('<comment>Files indexed.</comment>');
    }

    public static function commit($input, $output, $msg)
    {
        if (empty($msg)) {
            $msg = 'No comment, committed from script at ' . date('Y-m-d H:i:s');
        }

        $output->writeln('<info>[Git] Committing changes...</info>');
        $cmd = 'git commit -m "' . addslashes($msg) . '"';
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            // echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output->writeln('<comment>Code committed.</comment>');

    }

    public static function push($input, $output)
    {
        $output->writeln('<info>[Git] Pushing changes...</info>');
        $cmd = 'git push origin master';
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output->writeln('<comment>Code pushed.</comment>');
    }

}
