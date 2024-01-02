<?php

declare(strict_types = 1);

namespace myrpc\Response;

interface ResponseInterface
{
    /**
     * @throws \myrpc\Exception\ServiceException
     */
    public function getResponse(): object|array|bool|float|int|string|null;

    public function isSuccess(): bool;

    public function getError(): ?int;

    public function getRequestId(): ?string;
}
