<?php

declare(strict_types = 1);

namespace myrpc;

use myrpc\Handler\HandlerFactoryInterface;
use myrpc\Schema\SchemaFactoryInterface;

class SchemaServer
{
    //TODO add factory
    public function __construct(
        protected readonly HandlerFactoryInterface $handlerFactory,
        protected readonly SchemaFactoryInterface $schemaFactory
    ) {
    }

    /**
     * @throws \myrpc\Exception\ServiceException
     */
    public function getServiceSchema(string $service): ?string
    {
        $handler = $this->handlerFactory->create($service);
        $handlerWithSchema = $this->handlerFactory->hasSchema($handler);
        if ($handlerWithSchema) {
            $handler = $this->handlerFactory->withSchema($handlerWithSchema, $this->schemaFactory);
            $schema = $handler->getSchema();

            return (string) $schema;
        }

        return null;
    }
}
