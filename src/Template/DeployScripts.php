<?php
/**
 * useage:
 * butler template:deploy-script
 */
namespace Console\Template;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeployScripts extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('template:deploy-scripts')
            ->setDescription('Copy deploy script into working directory.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $cwd = getcwd();
        $helper = $this->getHelper('question');
        $question_start = new ConfirmationQuestion("<question>Shall I copy deploy script into current directory [$cwd]? (Y/n): </question>\n", true);

        if (!$helper->ask($input, $output, $question_start)) {
            $output->writeln('K, bye!');
            return;
        }

        // copy files
        $tempalte_dir = BUTLER_DIR . '/templates/deploy-scripts';

        // print_r($keywords);
        // foreach (glob($tempalte_dir . "/*") as $src) {
        //     $dest = './' . basename($src);
        //     Util::copyFileAndReplaceContent($src, $dest, array_keys($keywords), array_values($keywords));
        //     $output->writeln($src . ' => ' . $dest);
        // }

        $output->writeln('<comment>Deploy scripts copied.</comment>');

    }

}
