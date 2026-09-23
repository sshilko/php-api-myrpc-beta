<?php

declare(strict_types = 1);

namespace myrpc\Response;

final class SimpleResponse implements ResponseInterface
{

    public function __construct(
        protected object|array|bool|float|int|string|null $response,
        protected ?string $requestId,
        protected ?int $errorId
    ) {
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
    public function getResponse(): object|array|bool|float|int|string|null
    {
        return $this->response;
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
