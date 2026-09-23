<?php

declare(strict_types = 1);

namespace myrpc\Request;

final class SimpleRequest implements RequestInterface
{

    public function __construct(
        protected string $service,
        protected string $action,
        protected array $arguments,
        protected string $requestId,
        protected string $authenticationToken
    ) {
    }

    #[\Override]
    public function getService(): string
    {
        return $this->service;
    }

    #[\Override]
    public function getAction(): ?string
    {
        return $this->action;
    }

    #[\Override]
    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    #[\Override]
    public function getIdentityToken(): ?string
    {
        return $this->authenticationToken;
    }

    #[\Override]
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}
