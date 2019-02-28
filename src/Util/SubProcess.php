<?php

namespace Console\Util;

// use Console\Util\Git;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SubProcess
{
    /**
     * Fancy PUSH
     * @param string $rootDir (optional) absolute path for wp site root folder
     */
    public static function sync($input, $output, $rootDir = './')
    {
        try {
            \chdir($rootDir);
            self::exportDB($input, $output);
            Git::addAll($input, $output);
            Git::commit($input, $output, '[Butler] Server sync.');
            Git::push($input, $output);
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return;
        }
    }

    /**
     * Fancy PULL
     * @return void
     */
    public static function update($input, $output)
    {
        try {
            // Commit local changes.

            $output->writeln('<info>==== Updating local site ====</info>');

            $output->writeln('<info>Staging local changes.</info>');
            self::exportDB($input, $output);
            Git::addAll($input, $output);
            Git::commit($input, $output, '[Butler] Update preparation.');

            // This might incur conflicts, developers need to resolve and test them locally

            $output->writeln('<info>Pulling remote code.</info>');
            $output->writeln('<fg=white;bg=blue;options=bold>There might be conflicts that you need to resolve and test locally before pushing up again.</>');
            Git::pull($input, $output);

            // if no error thrown import db
            self::importDB($input, $output);
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return;
        }
    }

    public static function exportDB($input, $output)
    {
        $output->writeln('<info>Exporting database...</info>');
        if (!is_dir('sql')) {
            mkdir('sql');
        }

        $filename = 'sql/export.sql';
        $process = new Process(['wp', 'db', 'export', '--add-drop-table', '--extended-insert=FALSE', $filename]);
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();
    }

    public static function importDB($input, $output, $envFile = './.butler.env')
    {
        $output->writeln('<info>[db:import] Creating database.</info>');
        $cmd = "wp db create";
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        $config = Env::loadConfig($envFile);

        $sql_file = 'sql/export.sql';
        if (!file_exists($sql_file)) {
            $output->writeln('[db:import] No sql export found, skipping import.');
            return;
        }

        // import db from sql
        $output->writeln('[db:import] wp db import ' . $sql_file);
        $cmd = 'wp db import ' . $sql_file;
        $process = Process::fromShellCommandline($cmd);
        // $process->setWorkingDirectory('./');
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // replace url

        // update table prefix in wp_config.php
        $prefix = self::getPrefixFromSQL($input, $output, $sql_file);
        self::setTablePrefix($input, $output, $prefix);

        $output->writeln('<info>Site database imported.</info>');
        $output->writeln('Run <info>virtualhost create ' . $config['domain'] . ' ' . getcwd() . '</info>');
    }

    /**
     * get table prefix from a SQL file
     *
     * @return string prefix
     */
    public static function getPrefixFromSQL($input, $output, $sql_file)
    {

        $output->writeln('<info>Getting database prefix from ' . $sql_file . '...</info>');
        $query = file_get_contents($sql_file);
        preg_match('`wp_(.*)_commentmeta`', $query, $matches, 0);
        if (!isset($matches[1])) {
            return 'wp_'; //use default
        }

        $prefix = 'wp_' . $matches[1] . '_';
        return $prefix;
    }

    public static function setTablePrefix($input, $output, $prefix)
    {
        $output->writeln('<info>Renaming database prefix in wp-config.php</info>');
        $cmd = 'wp config set table_prefix ' . $prefix;
        $process = Process::fromShellCommandline($cmd);
        // $process->setWorkingDirectory('./site');
        $process->setTimeout(7200);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public static function replaceURL($input, $output, $newUrl)
    {
        // get current url stored in db
        $output->writeln('[replace-url] Getting the site url in database.');
        $cmd = 'wp option get siteurl';
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        $currentSiteUrl = trim($process->getOutput());

        // replace current url to url in wp-config
        $output->writeln('<info>[replace-url] Replacing site urls in the database...</info>');
        $cmd = 'wp search-replace "' . $currentSiteUrl . '" "' . $newUrl . '"';
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
    }

}
