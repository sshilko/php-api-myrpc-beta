<?php

declare(strict_types = 1);

namespace myrpc\Handler;

final class HandlerResponse implements HandlerResponseInterface
{
    public function __construct(
        protected object|array|string|int|float|bool|null $response,
        protected ?int $code,
        protected bool $isError
    ) {
    }

    #[\Override]
    public function getResponse(): object|array|string|int|float|bool|null
    {
        return $this->response;
    }

    #[\Override]
    public function isError(): bool
    {
        return $this->isError;
    }

    #[\Override]
    public function getErrorCode(): ?int
    {
        return $this->code;
    }
}
