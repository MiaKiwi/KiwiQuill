<?php

namespace App\Routes;

use Miakiwi\Kiwiquill\Controllers\PostsController;
use Miakiwi\Kiwiquill\Controllers\TagsController;
use Pecee\SimpleRouter\SimpleRouter;



// ----- APIv1 Posts ----- \\
SimpleRouter::group([
    'defaultParameterRegex' => '[\w\-\.\/]+' // Allow alphanumeric, hyphen, dots, and forward slashes in post paths
], function () {



    // --- List all posts --- \\
    SimpleRouter::get($_ENV['API_ROOT'] . 'v1/posts', [PostsController::class, 'index']);



    // --- List metadata of all posts --- \\
    SimpleRouter::get($_ENV['API_ROOT'] . 'v1/posts/metadata', [PostsController::class, 'indexMetadata']);



    // --- Get a specific post by its ID --- \\
    SimpleRouter::get($_ENV['API_ROOT'] . 'v1/posts/id/{id}', [PostsController::class, 'showById']);



    // --- Search for posts --- \\
    SimpleRouter::get($_ENV['API_ROOT'] . 'v1/posts/search/{path?}', [PostsController::class, 'search']);



    // --- Get the metadata for a specific post by path --- \\
    SimpleRouter::get($_ENV['API_ROOT'] . 'v1/posts/{path}/metadata', [PostsController::class, 'metadata']);



    // --- Get a specific post by path --- \\
    SimpleRouter::get($_ENV['API_ROOT'] . 'v1/posts/{path}', [PostsController::class, 'show']);
});



// ----- APIv1 Tags ----- \\
SimpleRouter::group([
    'defaultParameterRegex' => '[\w\-\.\/]+' // Allow alphanumeric, hyphen, dots, and forward slashes in tag paths
], function () {



    // --- List all tags --- \\
    SimpleRouter::get($_ENV['API_ROOT'] . 'v1/tags', [TagsController::class, 'index']);



});