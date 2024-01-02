<?php

declare(strict_types = 1);

namespace myrpc\Response;

use myrpc\Exception\ServiceException;
use function json_encode;
use const JSON_THROW_ON_ERROR;

class JsonRpcResponse implements ResponseInterface
{

    protected float|object|array|bool|int|string|null $response;

    public function __construct(
        object|array|string|int|float|bool|null $response,
        protected ?string $requestId,
        protected ?int $errorId
    ) {
        $this->response = $response;
    }

    public function isSuccess(): bool
    {
        return null === $this->errorId;
    }

    /**
     * @throws \myrpc\Exception\ServiceException
     */
    public function getResponse(): string
    {
        try {
            /** @var string|bool $encoded */
            $encoded = json_encode($this->response, JSON_THROW_ON_ERROR);
            assert(is_string($encoded));
            return $encoded;
        } catch (\Throwable $ex) {
            throw new ServiceException($ex->getMessage());
        }
    }

    public function getError(): ?int
    {
        return $this->errorId;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}
