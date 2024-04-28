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
use myrpc\Datatype\Internal\v1\Response\Success;
use myrpc\Handler\Context\Context;
use myrpc\Handler\Context\ContextFactoryInterface;
use myrpc\Handler\HandlerFactory;
use myrpc\Handler\HandlerResponseFactory;
use myrpc\Handler\SmartHandler;
use myrpc\Identity\IdentityInterface;
use myrpc\Identity\TokenIdentityFactory;
use myrpc\Request\RequestFactoryInterface;
use myrpc\Response\SimpleResponseFactory;
use myrpc\Schema\JsonSchemaFactory;
use myrpc\Schema\SchemaFactoryInterface;
use myrpc\SchemaServer;
use phpunit\includes\BaseTestCase;
use phpunit\includes\Handlers\v3\NormalWorker;
use phpunit\includes\MyBackendEnumHTTTPMethods;
use phpunit\includes\MyPlainEnum;
use phpunit\includes\MyUserlandDatatype;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;
use function is_object;
use function json_decode;
use function json_encode;
use function uniqid;
use function var_export;
use const JSON_THROW_ON_ERROR;

/**
 * @author Sergei Shilko <contact@sshilko.com>
 * @license https://opensource.org/licenses/mit-license.php MIT
 *
 * @see https://github.com/sshilko/php-api-myrpc
 *
 * @phpcs:disable SlevomatCodingStandard.Files.FileLength.FileTooLong
 */
final class NormalWorkerTest extends BaseTestCase
{
    /**
     * @dataProvider getUserlandDatatypesApiCalls
     */
    public function testDatatypesRequestResponseHandler(
        string $action,
        callable $arguments,
        IdentityInterface $id,
        callable $responseGen
    ): void {
        $this->service = $this->newApiServer($action, $arguments(), $id);
        $responseBody = $responseGen();

        $response = $this->service->run();
        if ($responseBody instanceof Throwable) {
            self::assertEquals(
                $responseBody->getMessage(),
                $response->getResponse(),
                'Received ' . var_export($response, true) . ' Expected string "' . $responseBody->getMessage() . '"'
            );
            self::assertEquals(
                $responseBody->getCode(),
                $response->getError(),
                'Received ' . var_export($response->getError(), true)
            );
            self::assertFalse($response->isSuccess());
        } else {
            if (is_object($responseBody)) {
                self::assertIsObject($response->getResponse(), 'expected to receive object');
            }
            self::assertEquals(
                $responseBody,
                $response->getResponse(),
                'Received ' . var_export($response->getResponse(), true) .
                ' Expected: ' . var_export($responseBody, true)
            );
            self::assertTrue($response->isSuccess());
        }
    }

    /**
     * @dataProvider getSimpleApiCalls
     */
    public function testSimpleRequestResponseHandler(
        string $action,
        array $arguments,
        IdentityInterface $id,
        $responseBody
    ): void {

        $this->service = $this->newApiServer($action, $arguments, $id);

        $response = $this->service->run();
        if ($responseBody instanceof Throwable) {
            self::assertEquals(
                $responseBody->getMessage(),
                $response->getResponse(),
                'Received ' . var_export($response, true)
            );
            self::assertEquals(
                $responseBody->getCode(),
                $response->getError(),
                'Received ' . var_export($response->getError(), true)
            );
            self::assertFalse($response->isSuccess());
        } else {
            if (is_object($responseBody)) {
                self::assertIsObject(
                    $response->getResponse(),
                    'expected to receive object' .
                    ' sent: ' . json_encode($arguments, JSON_THROW_ON_ERROR) .
                    ' got: ' . json_encode($response->getResponse(), JSON_THROW_ON_ERROR)
                );
            }
            self::assertEquals(
                $responseBody,
                $response->getResponse(),
                'Received ' . var_export($response->getResponse(), true) .
                ' Expected: ' . var_export($responseBody, true)
            );
            self::assertTrue($response->isSuccess());
        }
    }

    /**
     * @dataProvider getSimpleApiCalls
     * @throws \JsonException
     */
    public function testSimpleSchemaGeneration(): void
    {
        $service = uniqid('', true);
        $handler = new SmartHandler(new NormalWorker());

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with($service)->willReturn(true);
        $container->method('get')->with($service)->willReturn($handler);

        $datatypeFactory = $this->createMock(DatatypeFactoryInterface::class);
        $handlerFactory = new HandlerFactory($container, $datatypeFactory);

        $schemaServer = new SchemaServer(
            $handlerFactory,
            new JsonSchemaFactory($datatypeFactory),
        );

        $response = $schemaServer->getServiceSchema($service);

        self::assertIsObject(
            json_decode((string) $response, false, 512, JSON_THROW_ON_ERROR),
            'Received ' . var_export($response, true)
        );
    }

    /**
     * @dataProvider getSimpleMethodValidations
     * @throws \JsonException
     */
    public function testSimpleMethodArgumentValidations(string $action, array $args, $id, string $err, int $code): void
    {

        $this->service = $this->newApiServer($action, $args, $id);

        $response = $this->service->run();

        self::assertEquals($code, $response->getError(), $response->getResponse());
        self::assertEquals($err, $response->getResponse());
        self::assertFalse($response->isSuccess());
    }

    public static function getSimpleMethodValidations(): array
    {
        $token = uniqid('any-auth-token', true);
        $id = (new TokenIdentityFactory())->create($token);

        return [
            'inputArray' => [
                'inputArray',
                [1, 2, 3, 'a', 'b', 'c'],
                $id,
                'Array argument type is not supported, please wrap \'myPlainArray\' in typed object',
                1000,
            ],
            'inputEnum1' => [
                'acceptPlainEnumArguments',
                [MyPlainEnum::POST],
                $id,
                'Non-backend enum argument type is not supported, check \'plainEnum1\' definition',
                1000,
            ],

            'inputEnum2' => [
                'acceptBackendEnumArguments',
                ['invalidBackenEnumValue'],
                $id,
                'Enum value "invalidBackenEnumValue" is not defined for enum argument \'backedEnum1\'',
                1000,
            ],

            'inputIntOrString' => [
                'inputIntOrString',
                ['stringOrIntInput'],
                $id,
                'Union input argument \'myIntOrStringInput\' is not supported',
                1000,
            ],

        ];
    }

    public static function getSimpleApiCalls(): array
    {
        $token = uniqid('any-auth-token', true);
        $id = (new TokenIdentityFactory())->create($token);

        $inputNamedArgumentsResponse = (object) [
            'mybool3' => false,
            'myfloat4' => 1.2,
            'myint2' => 456,
            'mystring1' => 'str1',
        ];
        $success1 = new Success($inputNamedArgumentsResponse);

        //TODO: fix arrays with symfony serializer
        //$bookInput->bookRatings = ['boggle' => 4, 'msdn' => 'yes', 'usr' => 1.1];
        //$bookInput->recentSalesDates = ['2011-01-01', 'abc', 888];

        return [

            'input5or6' => [
                'input5or6',
                [5],
                $id,
                5,
            ],
            'inputBool'    => ['inputBool',       [false],         $id,       false],

            'inputDate' => [
                'inputDate',
                ['2011-01-01'],
                $id,
                '2011-01-01',
            ],

            'inputEnum2' => [
                'acceptBackendEnumArguments',
                ['get'],
                $id,
                // we do NOT return typed responses, only value of enum
                MyBackendEnumHTTTPMethods::POST->value,
            ],
            'inputFloat'   => ['inputFloat',      [4.56],          $id,       4.56],
            'inputInt'     => ['inputInt',        [456],           $id,       456],

            'inputNamedArguments' => [
                'inputNamedArguments',
                ['str1', 456, false, 1.2],
                $id,
                $success1,
            ],
            #name           #action           #arguments           #identity  #response
            'inputNull'    => ['inputNull',       [null],          $id,       null],
            'inputString'  => ['inputString',     ['string2'],     $id,       'string2'],

            'inputStringBetween1And5Characters' => [
                'inputStringBetween1And5Characters',
                ['abc'],
                $id,
                'abc',
            ],

            'inputUUIDorEmail' => [
                'inputUUIDorEmail',
                ['aa@bb.com'],
                $id,
                'aa@bb.com',
            ],

            'inputUUIDorEmail' => [
                'inputUUIDorEmail',
                ['b7359320-5e92-4121-b818-4cae1e98b748'],
                $id,
                'b7359320-5e92-4121-b818-4cae1e98b748',
            ],
        ];
    }

    /**
     * @throws \JsonException
     */
    public static function getUserlandDatatypesApiCalls(): array
    {
        $token = uniqid('any-auth-token', true);
        $id = (new TokenIdentityFactory())->create($token);

        $bookInput = new MyUserlandDatatype("author2", 18, false, 1.99);
        $bookArg = json_decode(
            json_encode($bookInput, JSON_THROW_ON_ERROR),
            false,
            JSON_THROW_ON_ERROR,
            JSON_THROW_ON_ERROR
        );

        return [

            /**
             * Test input serializer/unmarshalling validation
             */
            'inputCustomDatatype but incomplete payload provided as input' => [
                'getMyUserlandDatatype',
                static function () use ($bookArg) {
                    $bookWithoutProperty = clone $bookArg;
                    unset($bookWithoutProperty->authorAge, $bookWithoutProperty->authorName);

                    return [$bookWithoutProperty];
                },
                $id,
                static function () {
                    return new RuntimeException("Missing properties in 'book': authorAge, authorName", 1000);
                },
            ],

            /**
             * Test input serializer/unmarshalling validation with nullable properties
             */
            'inputCustomDatatype with optional and nullable property returns OK' => [
                'getMyUserlandDatatype',
                static function () use ($bookArg) {
                    $bookWithoutPropertyNulalbleOptional = clone $bookArg;
                    unset($bookWithoutPropertyNulalbleOptional->bookPrice);

                    return [$bookWithoutPropertyNulalbleOptional];
                },
                $id,
                static function () use ($bookInput) {
                    $bookInputOptional = clone $bookInput;
                    $bookInputOptional->bookPrice = null;
                    $bookInputOptional->verified = true;

                    return $bookInputOptional;
                },
            ],
            /**
             * Test simple input->output processing
             */
            'inputCustomDatatype' => [
                'getMyUserlandDatatype',
                static function () use ($bookArg) {
                    return [$bookArg];
                },
                $id,
                static function () use ($bookInput) {
                    $bookOutput = clone $bookInput;
                    $bookOutput->verified = true;

                    return $bookOutput;
                },
            ],

            /**
             * Test validator for input using loadValidatorMetadata (Symfony Specific Validator Feature)
             */
            'inputCustomDatatype with attributes validation using Symfony validator loadValidatorMetadata' => [
                'getMyUserlandDatatype',
                static function () use ($bookArg) {
                    $bookYoungAuthor = clone $bookArg;
                    $bookYoungAuthor->authorAge = 1;

                    return [$bookYoungAuthor];
                },
                $id,
                static function () {
                    return new RuntimeException("Validation failed in 'book': authorAge too low", 1000);
                },
            ],

            /**
             * Test validator for input using PHP8 Attributes
             */
            'inputCustomDatatype with attributes validation using Symfony validator attributes' => [
                'getMyUserlandDatatype',
                static function () use ($bookArg) {
                    $bookWithoutAuthor = clone $bookArg;
                    $bookWithoutAuthor->authorName = "";

                    return [$bookWithoutAuthor];
                },
                $id,
                static function () {
                    return new RuntimeException(
                        "Validation failed in 'book': authorName author name cant be blank",
                        1000
                    );
                },
            ],
        ];
    }

    protected function newApiServer(string $action, array $arguments, IdentityInterface $id): ApiServer
    {
        $service = uniqid('', true);

        $worker = new NormalWorker();
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

        return new ApiServer(
            $handlerFactory,
            $requestFactory,
            $responseFactory,
            $identityFactory,
            $schemaFactory,
            $contextFactory,
            $logger
        );
    }
}
