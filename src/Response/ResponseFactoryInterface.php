<?php

declare(strict_types = 1);

namespace myrpc\Response;

use Throwable;

interface ResponseFactoryInterface
{
    public function createSuccessResponse(
        object|array|string|int|float|bool|null $data,
        ?string $requestId = null
    ): ResponseInterface;

    public function createErrorResponse(
        object|array|string|int|float|bool|null $data,
        ?int $errorId = null,
        ?string $requestId = null
    ): ResponseInterface;

    public function createExceptionResponse(Throwable $e, int $errorId, ?string $requestId = null): ResponseInterface;
}
