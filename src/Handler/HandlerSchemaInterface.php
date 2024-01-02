<?php

declare(strict_types = 1);

namespace myrpc\Handler;

use myrpc\Schema\SchemaFactoryInterface;
use myrpc\Schema\SchemaInterface;

interface HandlerSchemaInterface
{
    public function setSchemaFactory(SchemaFactoryInterface $schemaFactory): HandlerWithSchemaInterface;

    public function getSchema(): SchemaInterface;
}
