<?php 
$rootAutoload = __DIR__ . '/../vendor/autoload.php';
$publicAutoload = __DIR__ . '/../public/vendor/autoload.php';
if (file_exists($rootAutoload)) {
    require_once $rootAutoload;
} elseif (file_exists($publicAutoload)) {
    require_once $publicAutoload;
}

if (class_exists('Dotenv\\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
}

// Load config file
require_once 'config/config.php';
require_once 'config/error_config.php';
// Load helpers
require_once 'helpers/helpers.php';

// Auto Load Classes
spl_autoload_register(function($className) {
   require_once 'libraries/' . $className . '.php';
});