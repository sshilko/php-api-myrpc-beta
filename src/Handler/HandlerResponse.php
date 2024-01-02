<?php

declare(strict_types = 1);

namespace myrpc\Handler;

class HandlerResponse implements HandlerResponseInterface
{
    public function __construct(
        protected object|array|string|int|float|bool|null $response,
        protected ?int $code,
        protected bool $isError
    ) {
    }

    public function getResponse(): object|array|string|int|float|bool|null
    {
        return $this->response;
    }

    public function isError(): bool
    {
        return $this->isError;
    }

    public function getErrorCode(): ?int
    {
        return $this->code;
    }
}
