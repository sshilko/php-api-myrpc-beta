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

namespace phpunit\includes\Handlers\v1;

use myrpc\Handler\HandlerInterface;
use myrpc\Handler\HandlerResponseInterface;
use myrpc\Identity\IdentityInterface;

/**
 * @author Sergei Shilko <contact@sshilko.com>
 * @license https://opensource.org/licenses/mit-license.php MIT
 *
 * @see https://github.com/sshilko/php-api-myrpc
 */
class BasicHandler implements HandlerInterface
{
    /**
     * @throws \myrpc\Exception\ServiceActionException
     */
    public function action(
        string $action,
        ?array $arguments = null,
        ?IdentityInterface $id = null
    ): HandlerResponseInterface {
        switch ($action) {
            case 'getNull':
                return $this->wrapResponse(null);
            case 'getArray':
                return $this->wrapResponse([]);
            case 'getBool':
                return $this->wrapResponse(true);
            case 'getString':
                return $this->wrapResponse('string');
            case 'getInt':
                return $this->wrapResponse(123);
            case 'getFloat':
                return $this->wrapResponse(1.23);
            case 'getError':
                return $this->wrapResponse('', true, 1);
            case 'getErrorNull':
                return $this->wrapResponse('', true, null);
            case 'getIdentityToken':
                return $this->wrapResponse($id ? $id->getIdentityToken() : 'no-token');
            case 'getArguments':
                return $this->wrapResponse($arguments);
        }

        return $this->wrapResponse('unknown action', true, 2);
    }

    protected function wrapResponse($response, bool $error = false, ?int $errorCode = null): HandlerResponseInterface
    {
        return new class($response, $error, $errorCode) implements HandlerResponseInterface
        {
            public function __construct(
                private object|array|bool|float|int|string|null $response,
                private bool $error,
                private ?int $errorCode = null
            ) {
            }

            public function getResponse(): float|object|array|bool|int|string|null
            {
                return $this->response;
            }

            public function isError(): bool
            {
                return $this->error;
            }

            public function getErrorCode(): ?int
            {
                return $this->errorCode;
            }
        };
    }
}
