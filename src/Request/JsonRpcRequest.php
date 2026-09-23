<?php

declare(strict_types = 1);

namespace myrpc\Request;

use function filter_var;
use function is_string;
use function parse_url;
use function str_contains;
use const FILTER_SANITIZE_URL;
use const PHP_URL_FRAGMENT;
use const PHP_URL_PATH;

final class JsonRpcRequest implements RequestInterface
{

    protected ?string $action;

    protected string $service = '';

    public function __construct(
        ?string $action,
        protected ?array $arguments = null,
        protected ?string $requestId = null,
        protected ?string $authenticationToken = null
    ) {
        $this->action = null;
        if (is_string($action) && '' !== $action) {
            /** @psalm-suppress RedundantCast */
            $action = (string) filter_var($action, FILTER_SANITIZE_URL);
            if ('' !== $action && str_contains($action, '/')) {
                /**
                 * $action = 'v1/accounts/tickets#payment'
                 * --- converts into ---
                 * service = 'v1/accounts/tickets';
                 * action  = 'payment'
                 */
                $this->action  = (string) parse_url($action, PHP_URL_FRAGMENT);
                $this->service = (string) parse_url($action, PHP_URL_PATH);
            }
        }
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
