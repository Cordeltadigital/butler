<?php
/**
 * useage:
 * butler fe:starter [template]
 */
namespace Console\FrontEnd;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Starter extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('fe:starter')
            ->setDescription('Initialise front-end development based on starter templates')
            ->setHelp('Initialise front-end development based on starter templates')
            ->addArgument('template', InputArgument::REQUIRED, 'Choose a starter template: wp:child-theme | wp:plugin | sp:vue ');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $template = $input->getArgument('template');
        if (!in_array($template, ['wp:child-theme', 'wp:plugin', 'sp:vue'])) {
            $output->writeln('<error>Invalid template name. Choose one from wp:child-theme|wp:plugin|sp:vue</error>');
        }
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
