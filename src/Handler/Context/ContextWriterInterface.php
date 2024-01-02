<?php

declare(strict_types = 1);

namespace myrpc\Handler\Context;

use myrpc\Identity\IdentityInterface;

interface ContextWriterInterface
{
    public function setIdentity(?IdentityInterface $identity): void;

    public function setAction(string $action): void;

    public function setArguments(?array $arguments = null): void;
}
