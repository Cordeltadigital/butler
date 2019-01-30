<?php
/**
 * Import from a flywheel backup
 * usage:
 * butler migrate:flywheel backup.zip
 */
namespace Console\Migrate;

use Console\Util\Env;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Flywheel extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('migrate:flywheel')
            ->setDescription('Migrate a site from Flywheel using the backup zip file.')
            ->setHelp('Migrate a site from Flywheel using the backup zip file.')
            ->addArgument('zipFile', InputArgument::REQUIRED, 'File path to the zip file. e.g. /xxx/xxx/backup.zip');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $envFile = './.butler.env';
        if (!file_exists($envFile)) {
            $output->writeln('<error>.butler.env file doesn\'t exist, run `butler env` to generate the file.`</error>');
            return;
        }
        $config = Env::loadConfig($envFile);
        $newSiteUrl = 'http://' . $config['domain'];

        /**
         * Steps:
         * 1. check if zip file exist
         * 2. unzip to flywheel folder
         * 3. check if backup.sql and file/wp-content folder exist
         * 4. copy wp-content folder over
         * 5. get wp prefix
         * 6. import backup.sql
         * 7. change prefix in wp-config.php
         * 8. search replace site url
         */
        // 1. check if zip file exist
        $zipFile = $input->getArgument('zipFile');
        if (!\file_exists($zipFile)) {
            $output->writeln('<error>Backup file ' . $zipFile . ' not found.</error>');
            return;
        }

        // 2. unzip to flywheel folder
        $flywheel_dir = './flywheel';

        $output->writeln('<info>Unzipping flywheel backup file.</info>');
        $cmd = 'unzip ' . $zipFile . ' -d ' . $flywheel_dir;
        $process = Process::fromShellCommandline($cmd);
        $process->setWorkingDirectory('./');
        $process->setTimeout(7200);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // 3. check if backup.sql and file/wp-content folder exist
        if (!is_dir($flywheel_dir)) {
            $output->writeln('<error>No flywheel folder found.</error>');
            return;
        }

        $sql_file = $flywheel_dir . '/backup.sql';
        if (!file_exists($flywheel_dir . '/backup.sql')) {
            $output->writeln('<error>No flywheel backup.sql found.</error>');
            return;
        }
        $wp_content_path = $flywheel_dir . '/files/wp-content';
        if (!is_dir($wp_content_path)) {
            $output->writeln('<error>No flywheel wp-content folder found.</error>');
            return;
        }

        // 4. copy wp-content folder over
        $output->writeln('<info>Copying flywheel files over.</info>');
        $cmd = 'cp -r ' . $wp_content_path . ' .';
        $process = Process::fromShellCommandline($cmd);
        $process->setWorkingDirectory('./');
        $process->setTimeout(7200);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // 5. get wp prefix
        $output->writeln('<info>Getting database prefix...</info>');
        $query = file_get_contents($sql_file);
        preg_match('`wp_(.*)_commentmeta`', $query, $matches, 0);
        $prefix = 'wp_' . $matches[1] . '_';

        // 6. import backup.sql
        // 6.1 backup current db into sql tmp.sql file
        $temp_db_backup_file = 'sql/backup_before_flywheel.sql';
        $output->writeln('<info>Backing up current database tables...</info>');
        $cmd = 'wp db export --add-drop-table ' . $temp_db_backup_file;
        $process = Process::fromShellCommandline($cmd);
        $process->setWorkingDirectory('./');
        $process->setTimeout(7200);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output->writeln('<comment>Databased backed up in ' . $temp_db_backup_file . '</comment>');

        // 6.2 delete current tables
        $output->writeln('<info>Cleaning current database tables...</info>');
        $cmd = 'wp db clean --yes';
        $process = Process::fromShellCommandline($cmd);
        $process->setWorkingDirectory('./');
        $process->setTimeout(7200);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // 6.3 import flywheel tables
        $output->writeln('<info>Importing flywheel database...</info>');
        $cmd = 'wp db import ' . $sql_file;
        $process = Process::fromShellCommandline($cmd);
        $process->setWorkingDirectory('./');
        $process->setTimeout(7200);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // 7. change prefix in wp-config.php
        $output->writeln('<info>Renaming database prefix in wp-config.php</info>');
        $cmd = 'wp config set table_prefix ' . $prefix;
        $process = Process::fromShellCommandline($cmd);
        $process->setWorkingDirectory('./');
        $process->setTimeout(7200);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // 8. search replace site url
        // 8.1 get current site url
        $output->writeln('Getting the site url in database.');
        $cmd = 'wp option get siteurl';
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        $currentSiteUrl = $process->getOutput();
        // 8.2 replace url in database
        $output->writeln('<info>Replacing site urls in the database...</info>');
        $cmd = "wp search-replace $currentSiteUrl $newSiteUrl";
        $process = Process::fromShellCommandline($cmd);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
    }
}
