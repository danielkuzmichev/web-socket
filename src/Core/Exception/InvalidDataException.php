<?php

namespace App\Core\Exception;

use Exception;

class InvalidDataException extends Exception implements ReturnableException
{
    protected $code = 400;
}
