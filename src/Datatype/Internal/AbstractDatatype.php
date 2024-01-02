<?php

declare(strict_types = 1);

namespace myrpc\Datatype\Internal;

use myrpc\Datatype\DatatypeInterface;
use myrpc\Validator\IsValidatableInterface;

abstract class AbstractDatatype implements DatatypeInterface, IsValidatableInterface
{
    public function getPayload(): object|array|string|int|float|bool|null
    {
        return $this;
    }

    public function validatableObject(): object
    {
        return $this;
    }
}
