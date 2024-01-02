<?php

declare(strict_types = 1);

namespace myrpc\Handler;

use myrpc\Handler\Context\ContextInterface;
use myrpc\Schema\SchemaFactoryInterface;

interface HandlerFactoryInterface
{
    /**
     * @throws \myrpc\Exception\ServiceException
     */
    public function create(string $handlerId): HandlerInterface;

    public function hasContext(HandlerInterface $h): ?SmartHandlerInterface;

    /**
     * @throws \myrpc\Exception\ServiceException
     */
    public function withContext(SmartHandlerInterface $h, ContextInterface $ctx): SmartHandlerInterface;

    /**
     * @throws \myrpc\Exception\ServiceException
     */
    public function hasSchema(HandlerInterface $h): ?HandlerSchemaInterface;

    /**
     * @throws \myrpc\Exception\ServiceException
     */
    public function withSchema(HandlerSchemaInterface $h, SchemaFactoryInterface $schema): HandlerWithSchemaInterface;
}
