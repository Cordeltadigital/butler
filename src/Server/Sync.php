<?php
/**
 * Run
 * butler server:sync
 * periodically to make sure it stays the latest
 */
namespace Console\Server;

use Console\Util\Git;
use Console\Util\Output;
use Exception;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
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
            ->setDescription('Push changes made on dev server.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        Output::signature($output);
        try {
            $this->exportDB($input, $output);
            Git::addAll($input, $output);
            Git::commit($input, $output, '[Butler] Server sync.');
            Git::push($input, $output);
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return;
        }
    }

    private function exportDB($input, $output)
    {
        $output->writeln('<info>Exporting database...</info>');
        $command = $this->getApplication()->find('db:export');

        $arguments = [
            'command' => 'db:export',
        ];

        $input = new ArrayInput($arguments);
        return $command->run($input, $output);
    }
}
