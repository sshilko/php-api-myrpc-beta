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
use myrpc\Datatype\DatatypeFactoryInterface;
use myrpc\Handler\Context\ContextFactoryInterface;
use myrpc\Handler\HandlerFactory;
use myrpc\Identity\IdentityInterface;
use myrpc\Identity\TokenIdentityFactory;
use myrpc\Request\RequestFactoryInterface;
use myrpc\Response\SimpleResponseFactory;
use myrpc\Schema\SchemaFactoryInterface;
use phpunit\includes\BaseTestCase;
use phpunit\includes\Handlers\v1\BasicHandler;
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
final class BasicHandlerTest extends BaseTestCase
{
    /**
     * @dataProvider getSimpleApiCalls
     */
    public function testSimpleRequestResponseHandler(
        string $action,
        array $arguments,
        IdentityInterface $id,
        $responseBody
    ): void {

        $service = 'v1/basicService';
        $handler = new BasicHandler();

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
    }

    public static function getSimpleApiCalls(): array
    {
        $token = uniqid('any-auth-token', true);
        $id = (new TokenIdentityFactory())->create($token);

        return [
            'getArguments-1'     => ['getArguments',      [1,2,3],    $id,       [1,2,3]],
            'getArguments-2'     => ['getArguments',      ['',2,3],   $id,       ['',2,3]],
            'getArguments-3'     => ['getArguments',      [1,1.23,3], $id,       [1,1.23,3]],
            'getArguments-4'     => ['getArguments',      [[],null,true], $id,  [[],null,true]],
            'getArray'   => ['getArray',      [],                    $id,       []],
            'getBool'    => ['getBool',       [],                    $id,       true],

            #name                        #action     #arguments   #identity  #response
            'getError'      => ['getError',          [],          $id,       ''],
            'getErrorNull'  => ['getErrorNull',      [],          $id,       ''],
            'getFloat'   => ['getFloat',      [],                    $id,       1.23],

            'getIdentityToken'   => ['getIdentityToken',  [],          $id,       $token,],
            'getInt'     => ['getInt',        [],                    $id,       123],
            #name           #action           #arguments             #identity  #response
            'getNull'    => ['getNull',       [],                    $id,       null],
            'getString'  => ['getString',     [],                    $id,       'string'],
        ];
    }
}
