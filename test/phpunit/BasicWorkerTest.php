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

use myrpc\ApiServer;
use myrpc\Datatype\DatatypeFactory;
use myrpc\Datatype\DatatypeFactoryInterface;
use myrpc\Handler\Context\Context;
use myrpc\Handler\Context\ContextFactoryInterface;
use myrpc\Handler\HandlerFactory;
use myrpc\Handler\HandlerResponseFactory;
use myrpc\Handler\SmartHandler;
use myrpc\Identity\IdentityInterface;
use myrpc\Identity\TokenIdentityFactory;
use myrpc\Request\RequestFactoryInterface;
use myrpc\Response\SimpleResponseFactory;
use myrpc\Schema\SchemaFactoryInterface;
use phpunit\includes\BaseTestCase;
use phpunit\includes\Handlers\v2\BasicWorker;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use function uniqid;
use function var_export;

/**
 * @author Sergei Shilko <contact@sshilko.com>
 * @license https://opensource.org/licenses/mit-license.php MIT
 *
 * @see https://github.com/sshilko/php-api-myrpc
 */
final class BasicWorkerTest extends BaseTestCase
{
    private const CODE_UNKNOWN = 100000;

    /**
     * @dataProvider getSimpleApiCalls
     */
    public function testSimpleRequestResponseHandler(
        string $action,
        array $arguments,
        IdentityInterface $id,
        $responseBody,
        ?int $errorCode = self::CODE_UNKNOWN
    ): void {
        $service = 'v2/workerService';

        $worker = new BasicWorker();
        $handler = new SmartHandler($worker);

        $request = $this->newSimpleRequest($service, $action, $arguments, uniqid('', true), $id->getIdentityToken());

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with($service)->willReturn(true);
        $container->method('get')->with($service)->willReturn($handler);

        $datatypeFactory = $this->createMock(DatatypeFactoryInterface::class);

        $handlerFactory = new HandlerFactory($container, $datatypeFactory);
        $responseFactory = new SimpleResponseFactory();
        $identityFactory = new TokenIdentityFactory();

        $logger = $this->createMock(LoggerInterface::class);

        $schemaFactory = $this->createMock(SchemaFactoryInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects(self::once())->method('create')->willReturn($request);

        $contextFactory = $this->createMock(ContextFactoryInterface::class);

        $typeContainer = $this->createMock(ContainerInterface::class);

        $context = new Context(new HandlerResponseFactory(), new DatatypeFactory($typeContainer));
        $contextFactory->expects(self::once())->method('create')->willReturn($context);

        $this->service = new ApiServer(
            $handlerFactory,
            $requestFactory,
            $responseFactory,
            $identityFactory,
            $schemaFactory,
            $contextFactory,
            $logger
        );

        $response = $this->service->run();
        self::assertSame(
            $responseBody,
            $response->getResponse(),
            'Received ' . var_export($response->getResponse(), true)
        );

        if (self::CODE_UNKNOWN !== $errorCode) {
            self::assertFalse($response->isSuccess());
            self::assertSame($errorCode, $response->getError());
        } else {
            self::assertTrue($response->isSuccess(), var_export($response, true));
        }
    }

    public static function getSimpleApiCalls(): array
    {
        $token = uniqid('any-auth-token', true);
        $id = (new TokenIdentityFactory())->create($token);

        return [
            'getArray'   => ['getArray',      [],                    $id,       []],
            'getBool'    => ['getBool',       [],                    $id,       false],

            #name                        #action     #arguments      #identity  #response #errorCode
            'getError'       => ['getError',          [],             $id,       'error1', 555],
            'getErrorNull'   => ['getErrorNull',      [],             $id,       'error2', 0],
            'getFloat'   => ['getFloat',      [],                    $id,       4.56],
            'getInt'     => ['getInt',        [],                    $id,       456],

            'getMyIdentity'  => ['getMyIdentity',     [],             $id,       $token],
            #name           #action           #arguments             #identity  #response
            'getNull'    => ['getNull',       [],                    $id,       null],
            'getString'  => ['getString',     [],                    $id,       'string1'],
        ];
    }
}
