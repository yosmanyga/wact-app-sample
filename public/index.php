<?php

define ('PRIV_ROOT', dirname(__FILE__).'/../private');

define('WACT_CONFIG_DIRECTORY', PRIV_ROOT . '/config/');
define('LIB_ROOT', PRIV_ROOT . '/lib/');
define('APP_ROOT', PRIV_ROOT . '/app/');


define ('WACT_MAIN_DIR', dirname(__FILE__));
require_once LIB_ROOT . 'wact-0.2a/common.inc.php';

require_once APP_ROOT . 'app.controller.php';
$front = new AppPathInfoDispatchController();
$front->start();
?>