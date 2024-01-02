<?php

declare(strict_types = 1);

namespace myrpc\Identity;

class TokenIdentityFactory implements IdentityFactoryInterface
{
    public function create(?string $payload = null): ?IdentityInterface
    {
        /* @phan-suppress-next-line PhanSuspiciousTruthyString */
        return $payload ? new TokenIdentity($payload) : null;
    }
}
