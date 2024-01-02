<?php

declare(strict_types = 1);

namespace myrpc\Schema;

interface SchemaFactoryInterface
{
    public function newSchemaFromObject(object $obj): SchemaInterface;
}
