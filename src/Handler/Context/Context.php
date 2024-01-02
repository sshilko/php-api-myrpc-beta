<?php

declare(strict_types = 1);

namespace myrpc\Handler\Context;

use myrpc\Datatype\DatatypeInterface;
use myrpc\Datatype\UserspaceDatatypeFactoryInterface;
use myrpc\Handler\HandlerResponseFactoryInterface;
use myrpc\Handler\HandlerResponseInterface;
use myrpc\Identity\IdentityInterface;

class Context implements ContextInterface
{

    private ?IdentityInterface $identity = null;

    private string $action = '';

    private ?array $arguments = null;

    public function __construct(
        protected HandlerResponseFactoryInterface $handlerResponseFactory,
        protected UserspaceDatatypeFactoryInterface $userspaceDatatypeFactory
    ) {
    }

    public function newErrorResponse(
        DatatypeInterface|array|string|int|float|bool|null $response,
        int $errorCode = 0
    ): HandlerResponseInterface {
        return $this->handlerResponseFactory->createErrorResponse($response, $errorCode);
    }

    public function newSuccessResponse(
        DatatypeInterface|array|string|int|float|bool|null $response
    ): HandlerResponseInterface {
        return $this->handlerResponseFactory->createSuccessResponse($response);
    }

    public function newDatatypeResponse(string $typeName): ?DatatypeInterface
    {
        if ($this->userspaceDatatypeFactory->hasUserspaceType($typeName)) {
            return clone $this->userspaceDatatypeFactory->getUserspaceType($typeName);
        }

        return null;
    }

    public function setIdentity(?IdentityInterface $identity): void
    {
        $this->identity = $identity;
    }

    public function getIdentity(): ?IdentityInterface
    {
        return $this->identity;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function setArguments(?array $arguments = null): void
    {
        $this->arguments = $arguments;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getArguments(): ?array
    {
        return $this->arguments;
    }
}
