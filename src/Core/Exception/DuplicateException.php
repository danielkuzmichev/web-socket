<?php

namespace App\Core\Exception;

use Exception;

class DuplicateException extends Exception implements ReturnableException
{
    protected $code = 409;
}
