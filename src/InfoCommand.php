<?php
namespace Console;

use Console\Util\Env;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('info')
            ->setDescription('Show butler info');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->printInfo($output);
    }

    public function printInfo(OutputInterface $output)
    {
        // Env::getGlobalEnv();
        $globalEnvPath = Env::getGlobalEnvFilePath();
        $globalEnvFileExists = file_exists($globalEnvPath);
        $env = Env::loadConfig();
        $info = [
            'Butler version' => BUTLER_VER,
            'Global env path' => $globalEnvPath,
            'Global env file exists? ' => $globalEnvFileExists ? 'Yes' : 'No',
            'Loaded env' => json_encode($env),
            'Butler directory' => BUTLER_DIR,
        ];
        $output->writeln('====================');
        foreach ($info as $key => $value) {
            $output->writeln('<info>' . $key . ' => ' . $value . '</info>');
        }
        $output->writeln('====================');

    }

}
