<?php

declare(strict_types = 1);

namespace myrpc\Handler;

use myrpc\Identity\IdentityInterface;

interface HandlerInterface
{
    /**
     * @throws \myrpc\Exception\HandlerActionException
     */
    public function action(
        string $action,
        ?array $arguments = null,
        ?IdentityInterface $id = null
    ): HandlerResponseInterface;
}
