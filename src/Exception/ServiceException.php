<?php

declare(strict_types = 1);

namespace myrpc\Exception;

use RuntimeException;

class ServiceException extends RuntimeException
{

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $code = 1000;

    public function getLogMessage(): string
    {
        return $this->getMessage() . ' at ' . $this->getFile() . '#' . (string) $this->getLine();
    }
}
