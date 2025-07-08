<?php

namespace Miakiwi\Kiwiquill\Exceptions;

use MiaKiwi\Kaphpir\Errors\Http\BadRequest;



class InvalidPaginationParametersError extends BadRequest
{
    /**
     * InvalidPaginationParametersError constructor.
     * @param string $message The error message.
     */
    public function __construct(string $message = "Invalid pagination parameters.")
    {
        parent::__construct($message);
    }
}