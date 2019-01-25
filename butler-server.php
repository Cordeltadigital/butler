<?php
/**
 * run these commands on server
 */
require_once __DIR__ . '/vendor/autoload.php';

use Console\FrontEnd\Starter;
use Console\SayHi;
use Symfony\Component\Console\Application;

$app = new Application('Cordelta Digital Dev Butler', 'v1.0.0');
$app->add(new SayHi());
$app->add(new Starter());
$app->run();
