<?php
namespace Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ButlerApplication extends Application
{
    public function __construct()
    {
        parent::__construct('Cordelta Digital Dev Butler', 'v' . BUTLER_VER);
    }

    /**
     * @param  InputInterface    $input
     * @throws \RuntimeException
     * @return string
     */
    private function getNewWorkingDir(InputInterface $input)
    {
        $workingDir = $input->getParameterOption(array('--working-dir', '-d'));
        if (false !== $workingDir && !is_dir($workingDir)) {
            throw new \RuntimeException('Invalid working directory specified, ' . $workingDir . ' does not exist.');
        }
        return $workingDir;
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('--working-dir', '-d', InputOption::VALUE_REQUIRED, 'If specified, use the given directory as working directory.'));

        return $definition;
    }

    /**
     * Initializes all the composer commands.
     */
    protected function getDefaultCommands()
    {
        $commands = array_merge(parent::getDefaultCommands(), [
            new \Console\DB\Export(),
            new \Console\DB\Import(),
            new \Console\Template\Import(),
            new \Console\Template\DeployScripts(),
            new \Console\Migrate\Flywheel(),
            new \Console\Server\Sync(),
            new \Console\EnvCommand(),
            new \Console\Takeover(),
            new \Console\Init(),
        ]);

        return $commands;
    }
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if ($newWorkDir = $this->getNewWorkingDir($input)) {
            $oldWorkingDir = getcwd();
            chdir($newWorkDir);
            $output->write("<info>Changed CWD to " . getcwd() . "</info>\n");
        }

        $result = parent::doRun($input, $output);

        if (isset($oldWorkingDir)) {
            chdir($oldWorkingDir);
        }

    }
}
