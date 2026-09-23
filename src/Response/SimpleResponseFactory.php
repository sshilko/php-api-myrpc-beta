<?php

declare(strict_types = 1);

namespace myrpc\Response;

use Throwable;

final class SimpleResponseFactory implements ResponseFactoryInterface
{
    #[\Override]
    public function createSuccessResponse(
        object|array|bool|float|int|string|null $data,
        ?string $requestId = null
    ): ResponseInterface {
        return new SimpleResponse($data, $requestId ?? '', null);
    }

    #[\Override]
    public function createErrorResponse(
        object|array|bool|float|int|string|null $data,
        ?int $errorId = null,
        ?string $requestId = null
    ): ResponseInterface {
        return new SimpleResponse($data, $requestId ?? '', $errorId ?? 0);
    }

    #[\Override]
    public function createExceptionResponse(Throwable $e, int $errorId, ?string $requestId = null): ResponseInterface
    {
        return new SimpleResponse($e->getMessage(), $requestId ?? '', $errorId);
    }
}
