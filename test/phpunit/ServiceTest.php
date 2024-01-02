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

namespace phpunit;

use myrpc\Exception\ServiceException;
use myrpc\Handler\HandlerInterface;
use myrpc\Handler\HandlerResponseInterface;
use myrpc\Identity\IdentityInterface;
use myrpc\Request\RequestInterface;
use phpunit\includes\BaseTestCase;
use RuntimeException;
use Throwable;
use function uniqid;

/**
 * @author Sergei Shilko <contact@sshilko.com>
 * @license https://opensource.org/licenses/mit-license.php MIT
 *
 * @see https://github.com/sshilko/php-api-myrpc
 */
final class ServiceTest extends BaseTestCase
{
    /**
     * @dataProvider getRunPayloads
     */
    public function testRunBehaviour($error, int $code): void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $handlerMock = $this->createMock(HandlerInterface::class);
        $identityMock = $this->createMock(IdentityInterface::class);
        $actionResult = $this->createMock(HandlerResponseInterface::class);

        $service = $requestId = $action = $authToken = $response = uniqid('', true);
        $payload = [];
        $arguments = [];

        $this->requestFactory->expects(self::once())->method('create')->with($payload)
            ->willReturn($requestMock);

        $requestMock->expects(self::once())->method('getArguments')->willReturn($arguments);
        $requestMock->expects(self::once())->method('getAction')->willReturn($action);
        $requestMock->expects(self::once())->method('getService')->willReturn($service);
        $requestMock->expects(self::once())->method('getRequestId')->willReturn($requestId);
        $requestMock->expects(self::once())->method('getIdentityToken')->willReturn($authToken);


        $this->handlerFactory->expects(self::once())->method('create')->with($service)->willReturn($handlerMock);

        $handlerMock->expects(self::once())->method('action')->with($action, $arguments, $identityMock)
            ->willReturn($actionResult);

        $this->identityFactory->expects(self::once())->method('create')->with($authToken)->willReturn($identityMock);


        if ($error instanceof ServiceException) {
            $actionResult->expects(self::once())->method('getResponse')->willThrowException($error);
            $this->logger->expects(self::once())->method('error');
            $this->responseFactory->expects(self::once())->method('createErrorResponse')->with(
                $error->getMessage(),
                $error->getCode(),
                $requestId
            );
        } elseif ($error instanceof Throwable) {
            $actionResult->expects(self::once())->method('getResponse')->willThrowException($error);
            $this->logger->expects(self::once())->method('critical');
            $this->responseFactory->expects(self::once())->method('createExceptionResponse')->with($error);
        } else {
            $actionResult->expects(self::once())->method('isError')->willReturn((bool) $error);
            $actionResult->expects(self::once())->method('getResponse')->willReturn($response);

            if (true === (bool) $error) {
                $actionResult->expects(self::once())->method('getErrorCode')->willReturn($code);
                $this->responseFactory->expects(self::once())->method('createErrorResponse')->with(
                    $response,
                    $code,
                    $requestId
                );
            } else {
                $this->responseFactory->expects(self::once())->method('createSuccessResponse')->with(
                    $response,
                    $requestId
                );
            }
            $this->logger->expects(self::never())->method('error');
            $this->logger->expects(self::never())->method('critical');
        }

        $this->service->run($payload);
    }

    public static function getRunPayloads(): array
    {
        return [
            'failure-response' => [1,     100],
            #name                 #error  #code
            'success-response' => [0,     0],
            'svc-err-failure'  => [new RuntimeException('world'), 300],
            'svc-err-response' => [new ServiceException('hello'), 200],
        ];
    }
}
