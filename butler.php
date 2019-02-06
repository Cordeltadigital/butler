<?php
date_default_timezone_set('Australia/Sydney');
define('BUTLER_DIR', __DIR__);
define('BUTLER_VER', '1.0a');

require_once BUTLER_DIR . '/vendor/autoload.php';
use Console\DB\Export as DBExport;
use Console\DB\Import as DBImport;
use Console\Init;
use Console\SayHi;
use Console\Takeover;
use Console\Template\Import as TemplateImport;
use Symfony\Component\Console\Application;

$app = new Application('Cordelta Digital Dev Butler', 'v' . BUTLER_VER);
$app->add(new SayHi());

// $app->add(new Starter());
// db:
$app->add(new DBImport());
$app->add(new DBExport());

// template:
$app->add(new TemplateImport());

// Migration
$app->add(new \Console\Migrate\Flywheel());

//Push
$app->add(new \Console\Server\Sync());

// Pull
$app->add(new \Console\Update());

$app->add(new \Console\EnvCommand());
$app->add(new \Console\Template\DeployScripts());
$app->add(new Takeover());
$app->add(new Init());
$app->run();
