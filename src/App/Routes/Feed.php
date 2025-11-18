<?php

namespace App\Routes;

use Miakiwi\Kiwiquill\Controllers\FeedController;
use Pecee\SimpleRouter\SimpleRouter;



// ----- RSS Feeds ----- \\
SimpleRouter::group([
    'defaultParameterRegex' => '[\w\-\.\/]+' // Allow alphanumeric, hyphen, dots, and forward slashes in feed paths
], function () {



    // --- Global feed --- \\
    SimpleRouter::get("feed", [FeedController::class, 'index']);



    // --- Tag-specific feed --- \\
    SimpleRouter::get("feed/{tag}", [FeedController::class, 'tag']);
});