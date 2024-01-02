<?php

declare(strict_types = 1);

namespace myrpc\Validator;

interface ValidatorInterface
{
    public function validate(IsValidatableInterface $input): array;
}
