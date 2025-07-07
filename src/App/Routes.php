<?php

namespace App;

use Framework\Logger;
use MiaKiwi\Kaphpir\ApiResponse\HttpApiResponse;
use MiaKiwi\Kaphpir\Errors\Http\Forbidden;
use MiaKiwi\Kaphpir\Errors\Http\InternalServerError;
use MiaKiwi\Kaphpir\Errors\Http\MethodNotAllowed;
use MiaKiwi\Kaphpir\Errors\Http\NotFound;
use MiaKiwi\Kaphpir\Errors\Http\Unauthorized;
use MiaKiwi\Kaphpir\Responses\v25_1_0\Response;
use MiaKiwi\Kaphpir\ResponseSerializer\JsonSerializer;
use Miakiwi\Kiwiquill\Containers\FSPostsContainer;
use Pecee\Http\Request;
use Pecee\SimpleRouter\SimpleRouter;



SimpleRouter::get('/', function () {
    ?>
    <pre><?php
    $fs_container = new FSPostsContainer($_ENV['POSTS_DIRECTORY']);


    // print_r($fs_container->getPosts());
    // print_r($fs_container->getPostById("001")->getPath());
    // print_r($fs_container->getPostsByTitle("API Authentication Guide")[0]->getKapirValue());
    // print_r($fs_container->getPostByPath("notes.md")?->getKapirValue());
});



// ----- Errors ----- \\
SimpleRouter::error(function (Request $request, \Exception $exception) {
    Logger::get()->critical("Routing error", [
        'exception' => [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ],
        'request' => [
            'method' => $request->getMethod(),
            'uri' => $request->getUrl(),
            'headers' => $request->getHeaders(),
        ]
    ]);



    switch ($exception->getCode()) {
        case 404:
            $error = new NotFound('Routing error.');
            $message = 'The requested resource was not found.';
            break;

        case 401:
            $error = new Unauthorized('Routing error.');
            $message = 'You are not authorized to access this resource.';
            break;

        case 403:
            if (preg_match('/or method \"(?:post|get|put|patch|delete|head)\" not allowed\./', $exception->getMessage())) {

                $error = new MethodNotAllowed('Routing error.');
                $message = 'The requested method is not allowed for this resource.';

            } else {

                $error = new Forbidden('Routing error.');
                $message = 'You do not have permission to access this resource.';

            }

            break;

        default:
            $error = new InternalServerError('Routing error.');
            $message = 'An internal error occurred.';
            break;
    }

    Logger::get()->error("Routing error", [
        'exception' => [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]
    ]);

    HttpApiResponse::send(
        JsonSerializer::getInstance(),
        (new Response())->error($error)->message($message)
    );

    die();
});