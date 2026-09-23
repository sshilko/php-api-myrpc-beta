<?php

declare(strict_types = 1);

namespace myrpc\Validator;

use Stringable;

final class SymfonyResult implements ValidatorResultInterface
{
    public function __construct(
        protected readonly string $name,
        protected readonly string $code,
        protected readonly string|Stringable $message
    ) {
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[\Override]
    public function getCode(): ?string
    {
        return $this->code;
    }

    #[\Override]
    public function getMessage(): string|Stringable
    {
        return $this->message;
    }
}
