<?php

namespace Console\DB;

use Console\Util\Env;
use Console\Util\SubProcess;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Console\Util\Git;

class Sync extends SymfonyCommand
{

    private $env_list = ['local', 'dev']; // 'prod'

    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('db:sync')
            ->setDescription('Sync local database with latest dev environment.');
            // ->addOption('from', null, InputArgument::OPTIONAL, 'Environment to export database: ' . implode( ' | ', $this->env_list), null)
            // ->addOption('to', null, InputArgument::OPTIONAL, 'Environment to import database: ' . implode( ' | ', $this->env_list), null);
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

        $from = 'dev';
        $to = 'local';

        $from_domain = $config[$from . '_domain'];
        // ssh butler@dev.cordelta.digital "cd /var/www/sample-site/site &&  " 
        // git pull origin master 
        // butler db:import

        $cmd = 'ssh butler@' . $from_domain.' cd /var/www/'.$config['site_slug'].'/site && wp db export --add-drop-table --extended-insert=FALSE ./sql/export.sql && git add . && git commit -m ":tophat: Butler db:sync" && git push origin master'; // @todo require developer to add their ssh key into dev server, building a web interface with authentication.
        
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        // git pull
        Git::pull($input, $output);
        
        // import
        SubProcess::importDB($input, $output);
        

        
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

        $new_url =   $target_domain;
        SubProcess::replaceURL($input, $output, $new_url);

    }
}