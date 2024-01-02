<?php

declare(strict_types = 1);

namespace myrpc\Handler;

use myrpc\Datatype\DatatypeFactoryInterface;
use myrpc\Exception\ServiceException;
use myrpc\Handler\Context\ContextInterface;
use myrpc\Schema\SchemaFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use function is_a;

class HandlerFactory implements HandlerFactoryInterface
{
    public function __construct(
        private ContainerInterface $handlersSource,
        private DatatypeFactoryInterface $datatypeFactory
    ) {
    }

    /**
     * @throws \myrpc\Exception\ServiceException
     */
    public function create(string $handlerId): HandlerInterface
    {
        if ('' === $handlerId) {
            throw new ServiceException('Missing handler id');
        }

        $ok = $this->handlersSource->has($handlerId);

        if (true === $ok) {
            try {
                $instance = $this->handlersSource->get($handlerId);
            } catch (NotFoundExceptionInterface $e) {
                throw new ServiceException(
                    'Handler ' . $handlerId . ' was not found in handler source ContainerInterface: ' . $e->getMessage()
                );
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceException(
                    'Failed retrieving ' . $handlerId . ' from handler source ContainerInterface: ' . $e->getMessage()
                );
            }

            if (!is_a($instance, HandlerInterface::class)) {
                throw new ServiceException('Handler ' . $handlerId . ' does not support ' . HandlerInterface::class);
            }

            return $instance;
        }

        throw new ServiceException('Handler ' . $handlerId . ' is not defined in source ContainerInterface');
    }

    public function hasSchema(HandlerInterface $h): ?HandlerSchemaInterface
    {
        if (is_a($h, HandlerSchemaInterface::class)) {
            return $h;
        }

        return null;
    }

    public function hasContext(HandlerInterface $h): ?SmartHandlerInterface
    {
        if (is_a($h, SmartHandlerInterface::class)) {
            return $h;
        }

        return null;
    }

    public function withContext(SmartHandlerInterface $h, ContextInterface $ctx): SmartHandlerInterface
    {
        $h->setContext($ctx);

        return $h;
    }

    public function withSchema(HandlerSchemaInterface $h, SchemaFactoryInterface $schema): HandlerWithSchemaInterface
    {
        return $h->setSchemaFactory($schema);
    }
}
