<?php

declare(strict_types = 1);

namespace myrpc\Handler;

use myrpc\Datatype\DatatypeInterface;

interface HandlerResponseFactoryInterface
{
    public function createErrorResponse(
        DatatypeInterface|array|string|int|float|bool|null $response,
        ?int $errorCode = null
    ): HandlerResponseInterface;

    public function createSuccessResponse(
        DatatypeInterface|array|string|int|float|bool|null $response
    ): HandlerResponseInterface;
}
