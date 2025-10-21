<?php

namespace App\Core\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Event
{
    public function __construct(public string $key)
    {
    }
}
