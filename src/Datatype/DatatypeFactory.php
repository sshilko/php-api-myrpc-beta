<?php

declare(strict_types = 1);

namespace myrpc\Datatype;

use myrpc\Datatype\Internal\v1\Response\Error;
use myrpc\Datatype\Internal\v1\Response\Success;
use myrpc\Exception\DatatypeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use function is_a;

class DatatypeFactory implements DatatypeFactoryInterface, UserspaceDatatypeFactoryInterface
{
    public function __construct(private ContainerInterface $typesSource)
    {
    }

    public function hasUserspaceType(string $typeName): bool
    {
        return $this->typesSource->has($typeName);
    }

    /**
     * @throws DatatypeException
     */
    public function getUserspaceType(string $typeName): DatatypeInterface
    {
        return $this->create($typeName);
    }

    public function getInternalErrorResponseType(string $message, ?int $code = null): DatatypeInterface
    {
        return new Error($message, $code);
    }

    public function getInternalSuccessResponseType(object|array|string|int|float|bool|null $response): DatatypeInterface
    {
        return new Success($response);
    }

    /**
     * @throws \myrpc\Exception\DatatypeException
     */
    protected function create(string $typeName): DatatypeInterface
    {
        if ('' === $typeName) {
            throw new DatatypeException('Missing datatype name');
        }

        try {
            $instance = $this->typesSource->get($typeName);
        } catch (NotFoundExceptionInterface $e) {
            throw new DatatypeException(
                'Datatype ' . $typeName . ' was not found in datatype source ContainerInterface: ' . $e->getMessage()
            );
        } catch (ContainerExceptionInterface $e) {
            throw new DatatypeException(
                'Failed retrieving ' . $typeName . ' from datatype source ContainerInterface: ' . $e->getMessage()
            );
        }

        if (!is_a($instance, DatatypeInterface::class)) {
            throw new DatatypeException('Datatype ' . $typeName . ' does not support ' . DatatypeInterface::class);
        }

        return $instance;
    }
}
