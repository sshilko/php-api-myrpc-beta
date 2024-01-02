<?php

declare(strict_types = 1);

namespace myrpc\Datatype;

interface UserspaceDatatypeFactoryInterface
{
    public function hasUserspaceType(string $typeName): bool;

    public function getUserspaceType(string $typeName): DatatypeInterface;
}
