<?php

namespace App\Core\Exception;

use Exception;

class NotFoundException extends Exception implements ReturnableException
{
    protected $code = 404;
}
