<?php

namespace App;

use Dotenv\Dotenv;
use Framework\Config;
use Framework\Logger;
use MiaKiwi\Kaphpir\Settings\DefaultSettings;
use Pecee\SimpleRouter\SimpleRouter;



// Set the error reporting level based on the LOG_LEVEL environment variable
if (!isset($_ENV['LOG_LEVEL']) || $_ENV['LOG_LEVEL'] !== 'debug') {
    // Hide all errors and warnings
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    // Show all errors and warnings
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Log the error
    Logger::get()->error("Error occurred", [
        'errno' => $errno,
        'errstr' => $errstr,
        'errfile' => $errfile,
        'errline' => $errline
    ]);
});



// Load the Composer autoloader
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';



// Load the environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');
$dotenv->load();



// Load the application configuration
Logger::get()->debug("Loading application configuration from file", [
    'file' => $_ENV['APP_CONFIG']
]);

Config::load($_ENV['APP_CONFIG']);



// Process Kaphpir settings
$kaphpir_settings = Config::get('kaphpir.settings', []);

foreach ($kaphpir_settings as $key => $value) {
    Logger::get()->debug("Setting Kaphpir setting", [
        'key' => $key,
        'value' => $value
    ]);

    DefaultSettings::getInstance()->setSetting($key, $value);
}



// Import the routes
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Routes.php';



// Configure CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET'); // Allow only GET requests since this is a read-only API



// Start the router
Logger::get()->debug("---------- Received request ----------", [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI']
]);

SimpleRouter::start();