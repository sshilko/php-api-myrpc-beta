<?php

declare(strict_types = 1);

namespace myrpc\Handler\Worker;

use myrpc\Handler\Context\ContextReaderInterface;

trait WorkerTrait
{
    protected ?ContextReaderInterface $context = null;

    public function setupWorker(ContextReaderInterface $context): void
    {
        $this->context = $context;
    }
}
