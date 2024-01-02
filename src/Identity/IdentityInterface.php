<?php

declare(strict_types = 1);

namespace myrpc\Identity;

interface IdentityInterface
{
    public function getIdentityToken(): ?string;
}
