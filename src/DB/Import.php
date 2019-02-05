<?php
/**
 * useage:
 * butler db [cmd]
 */
namespace Console\DB;

use Console\Util\Output;
use Console\Util\SubProcess;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('db:import')
            ->setDescription('Import latest sql/export.sql.')
            ->addOption('envFile', null, InputArgument::OPTIONAL, '.butler.env file path (default to current folder)', './.butler.env');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        Output::signature($output);
        $envFile = $input->getOption('envFile');
        if (!file_exists($envFile)) {
            $output->writeln('<error>[db:import] .butler.env file doesn\'t exist.</error>');
            return;
        }
        SubProcess::importDB($input, $output, $envFile);
    }

}
