<?php

declare(strict_types = 1);

namespace myrpc\Identity;

class TokenIdentity implements IdentityInterface
{

    public function __construct(protected string $token)
    {
    }

    public function getIdentityToken(): string
    {
        return $this->token;
    }
}
