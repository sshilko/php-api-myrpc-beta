<?php

declare(strict_types = 1);

namespace myrpc\Datatype\Internal\v1\Response;

use myrpc\Datatype\Internal\AbstractDatatype;

/**
 * This is generic success response
 */
class Success extends AbstractDatatype
{

    /**
     * This response will NOT have any type-specific schema for it's payload
     * Usage only with built-in types is recommended
     * Usage with array or object is NOT recommended
     */
    public function __construct(protected readonly object|array|string|int|float|bool|null $success)
    {
    }
}
