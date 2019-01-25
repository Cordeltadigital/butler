<?php
/**
 * useage:
 * butler db [cmd]
 */
namespace Console\DB;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
        if (!is_dir('sql')) {
            mkdir('sql');
        }
        // $filename = 'sql/'. date('YmdHis') . '.sql'; // git won't pickup any diffs coz it's in different files.

        $filename = 'sql/export.sql';
        $process = new Process(['wp', 'db', 'export', '--exclude_tables=wp_users', '--add-drop-table', $filename]);
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();

    }

    protected function greetUser(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            '==========================================',
        ]);

        // outputs a message without adding a "\n" at the end of the line
        $output->write('Hi, ' . $input->getArgument('username'));
    }
}
