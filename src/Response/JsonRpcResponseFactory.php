<?php

declare(strict_types = 1);

namespace myrpc\Response;

use Throwable;

class JsonRpcResponseFactory implements ResponseFactoryInterface
{
    public function createSuccessResponse(
        object|array|string|int|float|bool|null $data,
        ?string $requestId = null
    ): ResponseInterface {
        /**
         * JSON-RPC response object
         * @see https://www.jsonrpc.org/specification#response_object
         *
         * {
         *   "result": "response-string-on-success",
         *   "id": "3021d640-799a-4290-a964-b3924dbad4c1"
         * }
         *
         * {
         *   "error": 1001,
         *   "id": "3021d640-799a-4290-a964-b3924dbad4c2"
         * }
         */
        return new JsonRpcResponse($data, $requestId, null);
    }

    public function createErrorResponse(
        object|array|string|int|float|bool|null $data,
        ?int $errorId = null,
        ?string $requestId = null
    ): ResponseInterface {
        return new JsonRpcResponse($data, $requestId ?? '', $errorId ?? 0);
    }

    public function createExceptionResponse(Throwable $e, int $errorId, ?string $requestId = null): ResponseInterface
    {
        return new JsonRpcResponse($e->getMessage(), $requestId ?? '', $errorId);
    }
}
