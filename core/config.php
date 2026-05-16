<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

$configFile = ROOT_PATH . '/config.inc.php';

if (!file_exists($configFile)) {
    if (basename($_SERVER['SCRIPT_FILENAME']) !== 'install.php') {
        header('Location: /install.php');
        exit;
    }
    return;
}

$config = require $configFile;

define('DB_PATH', $config['db_path']);
define('HMAC_KEY', $config['hmac_key']);
define('INSTALLED', true);
