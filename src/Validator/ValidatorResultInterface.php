<?php

declare(strict_types = 1);

namespace myrpc\Validator;

use Stringable;

interface ValidatorResultInterface
{
    public function getName(): string;

    public function getCode(): ?string;

    public function getMessage(): string|Stringable;
}
