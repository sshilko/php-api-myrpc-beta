<?php

declare(strict_types = 1);

namespace myrpc\Datatype\Internal\v1\Response;

use myrpc\Datatype\Internal\AbstractDatatype;

class Error extends AbstractDatatype
{
    public function __construct(
        protected readonly object|array|string|int|float|bool|null $error,
        protected readonly ?int $code
    ) {
    }
}
