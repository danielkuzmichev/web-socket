<?php

namespace App\Core\Exception;

use Exception;

class DomainLogicalException extends Exception implements ReturnableException
{
    protected $code = 422;
}
