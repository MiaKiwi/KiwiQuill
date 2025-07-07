<?php

namespace Miakiwi\Kiwiquill\Exceptions;

use Exception;



class PostsContainerCreationException extends Exception
{
    /**
     * Constructor for the PostsContainerCreationException.
     * @param string $message The error message to display.
     * @param int $code The error code (default is 0).
     * @param Exception|null $previous The previous exception, if any (default is null).
     */
    public function __construct(string $message = "Failed to create posts container.", int $code = 0, ?Exception $previous = null)
    {
        // Call the parent constructor with the provided message, code, and previous exception.
        parent::__construct($message, $code, $previous);
    }
}