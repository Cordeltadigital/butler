<?php
/**
 * Run
 * butler server:sync
 * periodically to make sure it stays the latest
 */
namespace Console\Server;

use Console\Util\Output;
use Console\Util\SubProcess;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Sync extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('server:sync')
            ->setDescription('Push changes made on dev server.')
            ->addOption('rootDir', null, InputArgument::OPTIONAL, 'root directory of the site (default current directory).', './');

    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        Output::signature($output);
        $rootDir = $input->getOption('rootDir');
        SubProcess::sync($input, $output, $rootDir);
    }

}
