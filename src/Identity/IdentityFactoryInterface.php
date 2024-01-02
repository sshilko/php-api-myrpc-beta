<?php

declare(strict_types = 1);

namespace myrpc\Identity;

interface IdentityFactoryInterface
{
    public function create(?string $payload = null): ?IdentityInterface;
}
