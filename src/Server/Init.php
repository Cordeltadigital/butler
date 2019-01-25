<?php
/**
 * run this on server, make this wp instance consumption ready.
 */
namespace Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        // git init
        // git add remote origin xxxxx
        // generate .env file and include keys
        // export database
        // git push
    }

    public function initGit()
    {
    }
}
