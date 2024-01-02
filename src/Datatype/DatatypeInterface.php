<?php

declare(strict_types = 1);

namespace myrpc\Datatype;

interface DatatypeInterface
{
    public function getPayload(): object|array|string|int|float|bool|null;
}
