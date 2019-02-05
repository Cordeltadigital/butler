<?php
/**
 * useage:
 * butler db [cmd]
 */
namespace Console\DB;

use Console\Util\Output;
use Console\Util\SubProcess;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Export extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('db:export')
            ->setDescription('Export database to sql folder');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        Output::signature($output);
        SubProcess::exportDB($input, $output);

    }
}
