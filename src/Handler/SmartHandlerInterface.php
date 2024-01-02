<?php

declare(strict_types = 1);

namespace myrpc\Handler;

use myrpc\Handler\Context\ContextInterface;

interface SmartHandlerInterface extends HandlerWithSchemaInterface
{
    public function setContext(ContextInterface $context): void;
}
