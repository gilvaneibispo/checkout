<?php

namespace App\Exceptions;

use Exception;

class MissingFieldException extends Exception
{
    public function __construct()
    {
        parent::__construct("Missing field!", 404, null);
    }
}