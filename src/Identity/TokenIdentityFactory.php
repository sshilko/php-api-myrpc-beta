<?php

declare(strict_types = 1);

namespace myrpc\Identity;

use function is_string;

class TokenIdentityFactory implements IdentityFactoryInterface
{
    public function create(?string $payload = null): ?IdentityInterface
    {
        return is_string($payload) ? new TokenIdentity($payload) : null;
    }
}
