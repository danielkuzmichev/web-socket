<?php

namespace App\Util\Exception;

use Exception;

class NotFoundException extends Exception implements ReturnableException
{
    protected $code = 404;
}
