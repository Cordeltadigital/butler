<?php
namespace Console;

use Console\Util\Output;
use Console\Util\SubProcess;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('update')
            ->setDescription('Update local wp site with latest in git repository.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // check if there's .env file

        Output::signature($output);
        SubProcess::update($input, $output);
    }

}
