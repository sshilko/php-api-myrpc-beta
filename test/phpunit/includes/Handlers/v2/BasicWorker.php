<?php
/**
 * This file is part of the sshilko/php-api-myrpc package.
 *
 * (c) Sergei Shilko <contact@sshilko.com>
 *
 * MIT License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license https://opensource.org/licenses/mit-license.php MIT
 */

declare(strict_types = 1);

namespace phpunit\includes\Handlers\v2;

use myrpc\Handler\HandlerResponseInterface;
use myrpc\Handler\Worker\WorkerInterface;
use myrpc\Handler\Worker\WorkerTrait;

/**
 * @author Sergei Shilko <contact@sshilko.com>
 * @license https://opensource.org/licenses/mit-license.php MIT
 *
 * @see https://github.com/sshilko/php-api-myrpc
 */
class BasicWorker implements WorkerInterface
{

    use WorkerTrait;

    public function getNull(): mixed
    {
        return null;
    }

    public function getArray(): array
    {
        return [];
    }

    public function getBool(): bool
    {
        return false;
    }

    public function getString(): string
    {
        return 'string1';
    }

    /**
     * @throws \Exception
     */
    public function getInt(): int
    {
        return 456;
    }

    public function getFloat(): float
    {
        return 4.56;
    }

    public function getError(): ?HandlerResponseInterface
    {
        return $this->context?->newErrorResponse('error1', 555);
    }

    public function getErrorNull(): ?HandlerResponseInterface
    {
        return $this->context?->newErrorResponse('error2');
    }

    public function getMyIdentity(): null|string
    {
        return $this->context?->getIdentity()->getIdentityToken();
    }
}
