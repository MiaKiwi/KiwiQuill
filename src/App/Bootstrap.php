<?php

namespace App;

use Framework\Config;
use Framework\Logger;
use MiaKiwi\Kaphpir\Settings\DefaultSettings;



// Set the error reporting level based on the LOG_LEVEL environment variable
if (!isset($_ENV['LOG_LEVEL']) || $_ENV['LOG_LEVEL'] !== 'debug') {
    // Hide all errors and warnings
    error_reporting(0);
    ini_set('display_errors', '0');
}



// Process Kaphpir settings
$kaphpir_settings = Config::get('kaphpir.settings', []);

foreach ($kaphpir_settings as $key => $value) {
    Logger::get()->debug("Setting Kaphpir setting", [
        'key' => $key,
        'value' => $value
    ]);

    DefaultSettings::getInstance()->setSetting($key, $value);
}



require_once __DIR__ . DIRECTORY_SEPARATOR . 'Routes.php';