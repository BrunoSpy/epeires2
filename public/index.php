<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

define('ROOT_PATH', dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

/**
 * Display all errors when APP_ENV is development.
 */
if (getenv('APP_ENV') == 'development' || getenv('APP_ENV') == 'debug') {
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

// Setup autoloading
require 'init_autoloader.php';

if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', realpath(__DIR__ . '/../'));
}
$appConfig = include APPLICATION_PATH . '/config/application.config.php';

if (file_exists(APPLICATION_PATH . '/config/development.config.php')) {
    $appConfig = Laminas\Stdlib\ArrayUtils::merge($appConfig, include APPLICATION_PATH . '/config/development.config.php');
}

// Run the application!
Laminas\Mvc\Application::init($appConfig)->run();
