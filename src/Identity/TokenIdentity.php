<?php

declare(strict_types = 1);

namespace myrpc\Identity;

final class TokenIdentity implements IdentityInterface
{

    public function __construct(protected string $token)
    {
    }

    #[\Override]
    public function getIdentityToken(): string
    {
        return $this->token;
    }
}
