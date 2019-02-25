<?php
/**
 * generate continuous development scripts
 */

namespace Console\Generate;

use Console\Util\Env;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployScriptsGenerator extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('generate:deploy-scripts')
            ->setDescription('Generate deploy scripts into file system');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->copyDeployScripts();
    }

    public function copyDeployScripts()
    {
        // debug: show list of files

        $dir = BUTLER_DIR . '/src/stubs/deploy-scripts/';
        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $content = file_get_contents($dir . $file);

            $config = Env::loadConfig();
            print_r($config);
        }
    }

}
