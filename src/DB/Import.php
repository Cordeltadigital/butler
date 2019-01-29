<?php
/**
 * useage:
 * butler db [cmd]
 */
namespace Console\DB;

use Console\Util\Env as Env;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
        $envFile = $input->getOption('envFile');
        if (!file_exists($envFile)) {
            $output->writeln('<error>[db:import] .butler.env file doesn\'t exist.</error>');
            return;
        }
        $output->writeln('<info>[db:import] Creating database.</info>');
        $cmd = "wp db create";
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        $config = Env::loadConfig($envFile);
        $newSiteUrl = 'http://' . $config['domain'];

        if (!file_exists('./sql/export.sql')) {
            $output->writeln('[db:import] No sql export found, skipping import.');
            return;
        }

        // import db from sql
        $output->writeln('[db:import] wp db import sql/export.sql');
        $cmd = 'wp db import sql/export.sql';
        $process = Process::fromShellCommandline($cmd);
        $process->setWorkingDirectory('./');
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        // get current url stored in db
        $output->writeln('[db:import] Getting the site url in database.');
        $cmd = 'wp option get siteurl';
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        $currentSiteUrl = $process->getOutput();

        // replace current url to url in wp-config
        $output->writeln('<info>[db:import] Replacing site urls in the database...</info>');
        $cmd = "wp search-replace $currentSiteUrl $newSiteUrl";
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        $output->writeln('<info>If you have virtual hosts set up already, you should have a local site running at ' . $newSiteUrl . '</info>');
        $output->writeln('Run <info>virtualhost create ' . $config['domain'] . ' ' . getcwd() . '</info>');
    }

}
