<?php
date_default_timezone_set('Australia/Sydney');
define('BUTLER_DIR', __DIR__);

require_once BUTLER_DIR . '/vendor/autoload.php';
use Console\DB\Export as DBExport;
use Console\DB\Import as DBImport;
use Console\FrontEnd\Starter;
use Console\SayHi;
use Console\Server\Init;
use Console\Takeover;
use Console\Template\Import as TemplateImport;
use Symfony\Component\Console\Application;

$app = new Application('Cordelta Digital Dev Butler', 'v1.0.0');
$app->add(new SayHi());
$app->add(new Starter());
// db:
$app->add(new DBImport());
$app->add(new DBExport());

// template:
$app->add(new TemplateImport());

//
$app->add(new Takeover());
$app->add(new Init());
$app->run();
