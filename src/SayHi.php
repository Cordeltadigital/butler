<?php
namespace Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SayHi extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('hi')
            ->setDescription('Greet a user based on the time of the day.')
            ->setHelp('This command says hi')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user.')
            ->addArgument('last_name', InputArgument::OPTIONAL, 'Your last name?');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->greetUser($input, $output);
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
