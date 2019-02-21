<?php
date_default_timezone_set('Australia/Sydney');
define('BUTLER_DIR', __DIR__);
define('BUTLER_VER', '1.01a - @package_version@');

require_once BUTLER_DIR . '/vendor/autoload.php';

use Console\ButlerApplication;
$app = new ButlerApplication();
$app->run();
