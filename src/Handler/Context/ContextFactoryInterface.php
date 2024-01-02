<?php

declare(strict_types = 1);

namespace myrpc\Handler\Context;

interface ContextFactoryInterface
{
    public function create(): ContextInterface;
}
