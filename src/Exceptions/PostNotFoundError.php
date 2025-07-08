<?php

namespace Miakiwi\Kiwiquill\Exceptions;

use MiaKiwi\Kaphpir\Errors\Http\NotFound;



class PostNotFoundError extends NotFound
{
    public function __construct(string $message = "Post not found.", array $previous = [])
    {
        // Call the parent constructor with the provided message and previous exceptions.
        parent::__construct($message, $previous);
    }
}