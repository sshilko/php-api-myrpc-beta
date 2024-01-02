<?php

declare(strict_types = 1);

namespace myrpc\Validator;

interface IsValidatableInterface
{
    public function validatableObject(): object;
}
