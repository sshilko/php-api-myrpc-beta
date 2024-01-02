<?php

declare(strict_types = 1);

namespace myrpc\Schema;

use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/generic.html
 */
class JsonSchema implements SchemaInterface
{

    public function __construct(protected array $jsonSchema)
    {
    }

    /**
     * @throws \JsonException
     */
    public function __toString(): string
    {
        /** @var string|bool $encoded */
        $encoded = json_encode($this->jsonSchema, JSON_THROW_ON_ERROR);
        assert(is_string($encoded));
        return $encoded;
    }
}
