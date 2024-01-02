<?php

declare(strict_types = 1);

namespace myrpc\Serializer;

use stdClass;

interface SerializerInterface
{
    public function denormalize(stdClass $input, string $className): object;
}
