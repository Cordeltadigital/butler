<?php

namespace Console\DB;

use Console\Util\Env;
use Console\Util\SubProcess;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ReplaceURL extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('db:replace-url')
            ->setDescription('Replace site url in database.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $config = Env::loadConfig();

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
