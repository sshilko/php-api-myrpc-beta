<?php

declare(strict_types = 1);

namespace myrpc\Validator;

use Stringable;

class SymfonyResult implements ValidatorResultInterface
{
    public function __construct(
        protected readonly string $name,
        protected readonly string $code,
        protected readonly string|Stringable $message
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getMessage(): string|Stringable
    {
        return $this->message;
    }
}
