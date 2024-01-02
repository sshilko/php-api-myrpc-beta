<?php

declare(strict_types = 1);

namespace myrpc\Handler;

use myrpc\Datatype\DatatypeInterface;
use function is_a;
use function is_object;

//TODO test me
class HandlerResponseFactory implements HandlerResponseFactoryInterface
{
    public function createErrorResponse(
        object|array|string|int|float|bool|null $response,
        ?int $errorCode = null
    ): HandlerResponseInterface {
        return new HandlerResponse($response, $errorCode, true);
    }

    public function createSuccessResponse(
        object|array|string|int|float|bool|null $response,
        ?int $code = null
    ): HandlerResponseInterface {
        if (is_object($response) && is_a($response, DatatypeInterface::class)) {
            return new HandlerResponse($response->getPayload(), $code, false);
        }

        return new HandlerResponse($response, $code, false);
    }
}
