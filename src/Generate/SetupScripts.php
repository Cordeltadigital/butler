<?php
/**
 * generate continuous development scripts
 */

namespace Console\Generate;

use Console\Util\Env;
use Console\Util\File;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupScriptsGenerator extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('generate:scripts')
            ->setDescription('Generate setup scripts into file system');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->copyScripts();
    }

    public function copyScripts()
    {
        $keywords = [];

        $config = Env::loadConfig();
        foreach ($config as $key => $value) {
            $keywords['@' . $key . '@'] = $value;
        }

        $dir = BUTLER_DIR . '/src/stubs/setup/';
        $dest = './';
        $files = File::scan($dir);

        foreach ($files as $file) {
            $content = file_get_contents($dir . $file);

            // replace keywords

            $content = str_replace(array_keys($keywords), array_values($keywords), $content);

            $bytes = \file_put_contents($dest . $file, $content);

            if (!$bytes) {
                throw new \Exception('No luck putting files in place (' . $dest . $file . ').');
            }
        }
    }

}
