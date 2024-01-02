<?php

declare(strict_types = 1);

namespace myrpc\Request;

interface RequestFactoryInterface
{
    public function create(mixed $data = null): RequestInterface;
}
