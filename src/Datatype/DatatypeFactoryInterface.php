<?php

declare(strict_types = 1);

namespace myrpc\Datatype;

interface DatatypeFactoryInterface
{
    public function getInternalErrorResponseType(string $message, ?int $code = null): DatatypeInterface;

    public function getInternalSuccessResponseType(
        object|array|string|int|float|bool|null $response
    ): DatatypeInterface;
}
