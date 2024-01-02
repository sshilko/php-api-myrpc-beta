<?php

declare(strict_types = 1);

namespace myrpc\Handler;

interface HandlerResponseInterface
{
    public function getResponse(): object|array|string|int|float|bool|null;

    public function isError(): bool;

    public function getErrorCode(): ?int;
}
