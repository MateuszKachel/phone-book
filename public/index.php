<?php

define('PROJECT_ROOT', realpath('../'));
define('APP_START', microtime(true));

require_once(PROJECT_ROOT . '/config/setup.php');
require_once(PROJECT_ROOT . '/config/helpers.php');
require_once(PROJECT_ROOT . '/vendor/autoload.php');

(new App\App())->start();

