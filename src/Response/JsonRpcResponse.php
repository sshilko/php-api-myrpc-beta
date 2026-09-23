<?php

declare(strict_types = 1);

namespace myrpc\Response;

use myrpc\Exception\ServiceException;
use Throwable;
use function assert;
use function is_string;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final class JsonRpcResponse implements ResponseInterface
{

    protected float|object|array|bool|int|string|null $response;

    public function __construct(
        object|array|string|int|float|bool|null $response,
        protected ?string $requestId,
        protected ?int $errorId
    ) {
        $this->response = $response;
    }

    #[\Override]
    public function isSuccess(): bool
    {
        return null === $this->errorId;
    }

    /**
     * @throws \myrpc\Exception\ServiceException
     */
    #[\Override]
    public function getResponse(): string
    {
        try {
            /** @psalm-suppress RedundantConditionGivenDocblockType */
            $encoded = json_encode($this->response, JSON_THROW_ON_ERROR);
            /** @psalm-suppress RedundantConditionGivenDocblockType */
            assert(is_string($encoded));

            return $encoded;
        } catch (Throwable $ex) {
            throw new ServiceException($ex->getMessage());
        }
    }

    #[\Override]
    public function getError(): ?int
    {
        return $this->errorId;
    }

    #[\Override]
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}
