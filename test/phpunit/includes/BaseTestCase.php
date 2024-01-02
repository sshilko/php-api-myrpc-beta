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

namespace phpunit\includes;

use myrpc\ApiServer;
use myrpc\Datatype\DatatypeFactoryInterface;
use myrpc\Handler\Context\ContextFactoryInterface;
use myrpc\Handler\HandlerFactoryInterface;
use myrpc\Identity\IdentityFactoryInterface;
use myrpc\Request\RequestFactoryInterface;
use myrpc\Request\SimpleRequest;
use myrpc\Response\ResponseFactoryInterface;
use myrpc\Schema\SchemaFactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use function glob;

/**
 * @author Sergei Shilko <contact@sshilko.com>
 * @license https://opensource.org/licenses/mit-license.php MIT
 *
 * @see https://github.com/sshilko/php-api-myrpc
 */
class BaseTestCase extends TestCase
{

    protected MockObject|LoggerInterface $logger;

    protected ContextFactoryInterface $contextFactory;

    protected HandlerFactoryInterface $handlerFactory;

    protected RequestFactoryInterface $requestFactory;

    protected ResponseFactoryInterface $responseFactory;

    protected IdentityFactoryInterface $identityFactory;

    protected SchemaFactoryInterface $schemaFactory;

    protected DatatypeFactoryInterface $datatypeFactory;

    protected ApiServer $service;

    protected function newSimpleRequest(
        string $service,
        string $action,
        array $arguments,
        string $rid,
        string $auth
    ): SimpleRequest {
        return new SimpleRequest($service, $action, $arguments, $rid, $auth);
    }

    protected function setUp(): void
    {
        $this->handlerFactory = $this->createMock(HandlerFactoryInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $this->identityFactory = $this->createMock(IdentityFactoryInterface::class);
        $this->schemaFactory = $this->createMock(SchemaFactoryInterface::class);
        $this->contextFactory = $this->createMock(ContextFactoryInterface::class);

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new ApiServer(
            $this->handlerFactory,
            $this->requestFactory,
            $this->responseFactory,
            $this->identityFactory,
            $this->schemaFactory,
            $this->contextFactory,
            $this->logger
        );

        // manually include userspace handlers for testing
        foreach (glob(__DIR__ . '/Handlers/**/*.php') as $filename) {
            include_once $filename;
        }
    }
}
