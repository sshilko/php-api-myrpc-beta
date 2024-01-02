<?php

declare(strict_types = 1);

namespace myrpc\Handler\Worker;

use myrpc\Handler\Context\ContextReaderInterface;

interface WorkerInterface
{
    public function setupWorker(ContextReaderInterface $context): void;
}
