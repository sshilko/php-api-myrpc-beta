<?php

declare(strict_types = 1);

namespace myrpc\Handler\Context;

use myrpc\Datatype\UserspaceDatatypeFactoryInterface;
use myrpc\Handler\HandlerResponseFactoryInterface;

class ContextFactory implements ContextFactoryInterface
{
    public function __construct(
        protected HandlerResponseFactoryInterface $responseFactory,
        protected UserspaceDatatypeFactoryInterface $datatypeFactory
    ) {
    }

    public function create(): ContextInterface
    {
        return new Context($this->responseFactory, $this->datatypeFactory);
    }
}
