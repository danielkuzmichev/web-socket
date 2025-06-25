<?php

namespace App\Util\Exception;

use Exception;

class DuplicateException extends Exception implements ReturnableException
{
    protected $code = 409;
}
