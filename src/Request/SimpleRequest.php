<?php

declare(strict_types = 1);

namespace myrpc\Request;

class SimpleRequest implements RequestInterface
{

    public function __construct(
        protected string $service,
        protected string $action,
        protected array $arguments,
        protected string $requestId,
        protected string $authenticationToken
    ) {
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    public function getIdentityToken(): ?string
    {
        return $this->authenticationToken;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}
