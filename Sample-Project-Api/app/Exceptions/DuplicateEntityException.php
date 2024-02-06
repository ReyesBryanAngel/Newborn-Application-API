<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class DuplicateEntityException extends Exception
{
    public function _contstruct(string $message = "", int $code = 0, Throwable|nul $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}