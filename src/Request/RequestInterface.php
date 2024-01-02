<?php

declare(strict_types = 1);

namespace myrpc\Request;

interface RequestInterface
{
    public function getService(): string;

    public function getAction(): ?string;

    public function getArguments(): ?array;

    public function getIdentityToken(): ?string;

    public function getRequestId(): ?string;
}
