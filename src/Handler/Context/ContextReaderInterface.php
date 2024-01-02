<?php

declare(strict_types = 1);

namespace myrpc\Handler\Context;

use myrpc\Datatype\DatatypeInterface;
use myrpc\Handler\HandlerResponseInterface;
use myrpc\Identity\IdentityInterface;

interface ContextReaderInterface
{
    public function getIdentity(): ?IdentityInterface;

    public function getAction(): string;

    public function getArguments(): ?array;

    public function newErrorResponse(
        DatatypeInterface|array|string|int|float|bool|null $response,
        int $errorCode = 0
    ): HandlerResponseInterface;

    /**
     * Do not allow returning stdClass, all objects need to be typed
     */
    public function newSuccessResponse(
        DatatypeInterface|array|string|int|float|bool|null $response
    ): HandlerResponseInterface;

    /**
     * Use user-defined types as response
     */
    public function newDatatypeResponse(string $typeName): ?DatatypeInterface;
}
