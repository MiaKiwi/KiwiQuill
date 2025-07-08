<?php

namespace Miakiwi\Kiwiquill\Exceptions;

use MiaKiwi\Kaphpir\Errors\Http\InternalServerError;



class PostsContainerNotFoundError extends InternalServerError
{
    /**
     * Instantiate a new PostsContainerNotFoundError.
     * @param string $message The error message to display.
     * @param array $previous An array of previous exceptions to chain.
     */
    public function __construct(string $message = 'Posts container not found.', array $previous = [])
    {
        parent::__construct($message, $previous);
    }
}