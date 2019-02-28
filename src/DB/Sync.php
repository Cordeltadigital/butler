<?php

namespace Console\DB;

use Console\Util\Env;
use Console\Util\SubProcess;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class Sync extends SymfonyCommand
{

    private $env_list = ['local', 'dev', 'prod'];

    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('db:sync')
            ->setDescription('Sync database between environments.')
            ->addOption('from', null, InputArgument::OPTIONAL, 'Environment to export database: ' . implode($this->env_list, ' | '), null)
            ->addOption('to', null, InputArgument::OPTIONAL, 'Environment to import database: ' . implode($this->env_list, ' | '), null);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // print warning
        $output->writeln('<fg=red;bg=yellow>!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!</>');
        $output->writeln('<fg=red;bg=yellow>!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! Warning !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!</>');
        $output->writeln('<fg=red;bg=yellow>!!  This operation will overwrite database in the destination envirnoment  !!</>');
        $output->writeln('<fg=red;bg=yellow>!!                  Hope you know what you are doing                       !!</>');
        $output->writeln('<fg=red;bg=yellow>!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!</>');
        $output->writeln('<fg=red;bg=yellow>!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!</>');

        $helper = $this->getHelper('question');

        $config = Env::loadConfig();

        $from = $input->getOption('from');
        $to = $input->getOption('to');

        if (!$from || !\in_array($from, $this->env_list)) {
            // select from env
            $q = new ChoiceQuestion(
                'Please select the source environment for database (From). [Type number and enter]',
                $this->env_list
            );
            $q->setErrorMessage('Selection %s is invalid.');
            $from = $helper->ask($input, $output, $q);
        }
        $temp_array = $this->env_list;
        $from_index = array_search($from, $this->env_list);
        array_splice($temp_array, $from_index, 1);

        if (!$to || !\in_array($to, $this->env_list)) {
            // select to env
            $q = new ChoiceQuestion(
                'Please select the source environment for database (To). [Type number and enter]',
                $temp_array
            );
            $q->setErrorMessage('Selection %s is invalid.');
            $to = $helper->ask($input, $output, $q);
        }

        // replace url
        $domain_list = [];

        foreach ($config as $key => $value) {
            if (strpos($key, 'domain') !== false && !in_array($value, $domain_list)) {
                $domain_list[] = $value;
            }
        }

        // choose one from above to be the primary domain
        $q_domain = new ChoiceQuestion(
            'Please select the domain that will be served on this machine. [Type number and enter]',
            $domain_list
        );
        $q_domain->setErrorMessage('Selection %s is invalid.');
        $target_domain = $helper->ask($input, $output, $q_domain);

        $new_url = 'http://' . $target_domain;
        SubProcess::replaceURL($input, $output, $new_url);

    }
}
