<?php
namespace Console;

use Console\Util\Env;
use Console\Util\Output;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallCommand extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('install')->setDescription('Install WordPress after you have the files ready');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Env::loadConfig();

        $output->writeln('<info>Installing WordPress</info>');
        $cmd = 'wp core install --url=' . $config['domain'] . ' --title=' . $config['domain'] . ' --admin_user=butler --admin_password=butler --admin_email=noreply@cordelta.digital';

        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

}
