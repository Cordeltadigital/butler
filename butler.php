<?php
date_default_timezone_set('Australia/Sydney');

require_once __DIR__ . '/vendor/autoload.php';

use Console\DB\Export;
use Console\DB\Import;
use Console\FrontEnd\Starter;
use Console\SayHi;
use Console\Server\Init;
use Console\Takeover;
use Symfony\Component\Console\Application;

$app = new Application('Cordelta Digital Dev Butler', 'v1.0.0');
$app->add(new SayHi());
$app->add(new Starter());
$app->add(new Import());
$app->add(new Export());
$app->add(new Takeover());
$app->add(new Init());
$app->run();
