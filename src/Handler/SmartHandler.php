<?php

declare(strict_types = 1);

namespace myrpc\Handler;

use BackedEnum;
use myrpc\Datatype\DatatypeInterface;
use myrpc\Exception\HandlerActionArgumentsException;
use myrpc\Exception\HandlerActionException;
use myrpc\Exception\HandlerActionInputException;
use myrpc\Exception\HandlerActionSerializerException;
use myrpc\Exception\HandlerActionTypeException;
use myrpc\Exception\HandlerActionValidationException;
use myrpc\Exception\SerializerException;
use myrpc\Exception\ValidatorException;
use myrpc\Handler\Context\ContextInterface;
use myrpc\Handler\Worker\WorkerInterface;
use myrpc\Identity\IdentityInterface;
use myrpc\Schema\SchemaFactoryInterface;
use myrpc\Schema\SchemaInterface;
use myrpc\Serializer\JsonSerializer;
use myrpc\Serializer\SerializerInterface;
use myrpc\Validator\IsValidatableInterface;
use myrpc\Validator\Symfony;
use myrpc\Validator\ValidatorInterface;
use myrpc\Validator\ValidatorResultInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use stdClass;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use TypeError;
use function array_is_list;
use function array_key_exists;
use function array_shift;
use function assert;
use function call_user_func_array;
use function class_exists;
use function count;
use function enum_exists;
use function explode;
use function implode;
use function is_a;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_null;
use function is_object;
use function is_scalar;
use function is_string;
use function sort;
use function str_replace;
use function trim;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class SmartHandler implements SmartHandlerInterface
{

    protected ?ContextInterface $context = null;

    protected readonly ReflectionClass $reflectionClass;

    protected ?SchemaFactoryInterface $schemaFactory = null;

    protected readonly SerializerInterface $serializer;

    protected readonly ValidatorInterface $validator;

    public function __construct(
        protected readonly WorkerInterface $worker,
        ?SerializerInterface $serializer = null,
        ?ValidatorInterface $validator = null
    ) {
        $this->reflectionClass = new ReflectionClass($worker);
        $this->serializer = $serializer ?? new JsonSerializer();
        $this->validator = $validator ?? new Symfony();
    }

    /**
     * @throws \ReflectionException
     * @throws \myrpc\Exception\HandlerActionException
     * @phpcs:disable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
     */
    public function action(
        string $action,
        ?array $arguments = null,
        ?IdentityInterface $id = null
    ): HandlerResponseInterface {

        if ($this->context) {
            $this->context->setIdentity($id);
            $this->worker->setupWorker($this->context);
        }

        try {
            $method = $this->reflectionClass->getMethod($action);
            if ($method->isAbstract() || $method->isConstructor() || $method->isDestructor()) {
                throw new HandlerActionException('Action not found');
            }
        } catch (ReflectionException $e) {
            throw new HandlerActionException($e->getMessage());
        }

        $arguments = $this->prepareArguments($method, $arguments ?? []);

        try {
            $arguments = $this->resolveArguments($method, $arguments);
        } catch (ReflectionException $e) {
            throw new HandlerActionArgumentsException($e->getMessage());
        }

        try {
            /** @var array<array-key, mixed>|\myrpc\Datatype\DatatypeInterface|scalar|null $result */
            $result = call_user_func_array([$this->worker, $action], $arguments);
        } catch (TypeError $ex) {
            /**
             * This only happens if type conversion/mapping of input objects failed
             * Edge case, this error would have been caught earlier in conversion step
             */
            $actionMessage = explode(':', $ex->getMessage(), 3);
            $actionErrorMessage = $actionMessage[2] ?? '??';
            $message = str_replace(['(', ')'], '', "TypeError for action " . $actionErrorMessage);

            throw new HandlerActionTypeException($message);
        }

        if (is_a($result, HandlerResponseInterface::class)) {
            return $result;
        }

        if ($result instanceof BackedEnum) {
            assert($result instanceof BackedEnum);
            /**
             *
             * @psalm-suppress NoInterfaceProperties
             */
            $result = $result->value;
        }

        // TODO: validate response with same validator as request, we should never return non-valid state (code err)

        // TODO: disallow non-flat array returns, or possibly any array returns?
        /** @var array<array-key, mixed> $flatArray */
        $flatArray = $result;

        /** @psalm-suppress RedundantConditionGivenDocblockType */
        assert(
            $result instanceof DatatypeInterface ||
            is_null($result) ||
            is_scalar($result) ||
            (is_array($flatArray) && array_is_list($flatArray))
        );
        assert(!($result instanceof BackedEnum));
        assert(null !== $this->context);

        /** @var array<array-key, mixed>|\myrpc\Datatype\DatatypeInterface|scalar|null $result */
        return $this->context->newSuccessResponse($result);
    }

    public function setContext(ContextInterface $context): void
    {
        $this->context = $context;
    }

    public function setSchemaFactory(SchemaFactoryInterface $schemaFactory): HandlerWithSchemaInterface
    {
        $this->schemaFactory = $schemaFactory;

        return $this;
    }

    public function getSchema(): SchemaInterface
    {
        assert(null !== $this->schemaFactory);

        return $this->schemaFactory->newSchemaFromObject($this->worker);
    }

    protected function prepareArguments(ReflectionMethod $method, array $input): array
    {
        if (array_is_list($input)) {
            $listInput = [];
            foreach ($method->getParameters() as $i => $param) {
                if (array_key_exists($i, $input)) {
                    $inputValue = $input[$i];
                    assert(
                        is_object($inputValue) || is_string($inputValue) || is_int($inputValue) || is_float(
                            $inputValue
                        ) || is_bool(
                            $inputValue
                        ) || null === $inputValue
                    );
                    $listInput[$param->getName()] = $inputValue;
                }
            }

            return $listInput;
        }

        return $input;
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
     * @throws \ReflectionException
     * @throws \myrpc\Exception\HandlerActionException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function resolveArguments(ReflectionMethod $method, array $input): array
    {
        $output = [];
        if ($output === $input) {
            return $output;
        }

        $params = $method->getParameters();

        foreach ($params as $param) {
            $paramName = $param->getName();

            $paramType = $param->getType();
            if (null === $paramType) {
                throw new HandlerActionInputException('Type of argument ' . $paramName . ' is not defined');
            }

            if (!isset($input[$paramName])) {
                if ($param->isOptional()) {
                    $defaultParamValue = $param->getDefaultValue();
                    assert(
                        is_string($defaultParamValue) || is_int($defaultParamValue) || is_float(
                            $defaultParamValue
                        ) || is_bool(
                            $defaultParamValue
                        ) || null === $defaultParamValue
                    );
                    $output[$paramName] = $defaultParamValue;
                } elseif ($paramType->allowsNull()) {
                    $output[$paramName] = null;
                } else {
                    throw new HandlerActionInputException(
                        "Too few arguments for " . $method->getName() . ", expected argument: $paramName"
                    );
                }
            }

            if (is_a($paramType, ReflectionUnionType::class)) {
                throw new HandlerActionInputException('Union input argument \'' . $paramName . '\' is not supported');
            }

            if (!is_a($paramType, ReflectionNamedType::class)) {
                throw new HandlerActionInputException(
                    'Unsupported input argument ' . $paramName . ' type: ' . $paramType::class
                );
            }

            if ('array' === $paramType->getName()) {
                /**
                 * Supporting Array is bad decision, they are flat collections with free-form members
                 * Instead user should define explicitly what collections it is expecting, as user-defined objects
                 * And implement specific user-defined validations per collection: arrayOfInt, arrayOfString, ...
                 */
                throw new HandlerActionInputException(
                    'Array argument type is not supported, please wrap \'' . $paramName . '\' in typed object'
                );
            }

            if ($paramType->isBuiltin()) {
                /**
                 * PHP runtime will catch built-int typing errors, no need for extra validations of int, bool ...
                 */
                $mixedInput = $input[$paramName];
                assert(
                    is_object($mixedInput) || is_string($mixedInput) || is_int($mixedInput) || is_float(
                        $mixedInput
                    ) || is_bool(
                        $mixedInput
                    ) || null === $mixedInput
                );
                $output[$paramName] = $mixedInput;

                continue;
            }

            if (enum_exists($paramType->getName(), false)) {
                $reflectionClass = new ReflectionClass($paramType->getName());
                if ($reflectionClass->isEnum() && $reflectionClass->implementsInterface(BackedEnum::class)) {
                    /**
                     * Assume it is and verify later
                     *
                     */
                    $enumName = $paramType->getName();
                    assert($enumName instanceof BackedEnum);
                    assert(is_string($input[$paramName]) || is_int($input[$paramName]));
                    $reflectionEnumValue = $enumName::tryFrom($input[$paramName]);

                    if (null !== $reflectionEnumValue) {
                        $output[$paramName] = $reflectionEnumValue;
                    } else {
                        throw new HandlerActionInputException(
                            'Enum value "' . ((string) $input[$paramName]) .
                            '" is not defined for enum argument \'' . $paramName . "'"
                        );
                    }
                } else {
                    throw new HandlerActionInputException(
                        'Non-backend enum argument type is not supported, check \'' . $paramName . '\' definition'
                    );
                }
            }

            if (class_exists($paramType->getName())) {
                $reflectionClass = new ReflectionClass($paramType->getName());
                if ($reflectionClass->implementsInterface(DatatypeInterface::class)) {
                    /**
                     * Allow accepting only limited subset of user defined types, type conversion will happen later
                     */
                    try {
                        assert($input[$paramName] instanceof stdClass);
                        /* @phan-suppress-next-line PhanPartialTypeMismatchArgument */
                        $denormalized = $this->serializer->denormalize($input[$paramName], $paramType->getName());
                        /**
                         * We only expect instances of Datatype as input
                         */
                        assert($denormalized instanceof IsValidatableInterface);
                        $errors = $this->validator->validate($denormalized);
                        if (count($errors) > 0) {
                            $errorMessage = array_shift($errors);
                            assert($errorMessage instanceof ValidatorResultInterface);

                            $errorMessageBody = (string) $errorMessage->getMessage();

                            throw new HandlerActionValidationException(
                                "Validation failed in '$paramName': " .
                                $errorMessage->getName() . ' ' .
                                $errorMessageBody,
                            );
                        }
                        $output[$paramName] = $denormalized;
                    } catch (ValidatorException $e) {
                        throw new HandlerActionValidationException(
                            "ValidatorException in '$paramName': " . $e->getMessage()
                        );
                    } catch (SerializerException $e) {
                        $w = $e->getPrevious();
                        if ($w instanceof MissingConstructorArgumentsException) {
                            $missingProperties = $w->getMissingConstructorArguments();
                            sort($missingProperties);

                            throw new HandlerActionArgumentsException(
                                "Missing properties in '$paramName': " . trim(implode(', ', $missingProperties))
                            );
                        }

                        throw new HandlerActionSerializerException(
                            "Invalid argument payload in '$paramName': " . $e->getMessage()
                        );
                    }

                    continue;
                }
            }

            if (!array_key_exists($paramName, $output)) {
                throw new HandlerActionInputException(
                    "Argument '$paramName' for " . $method->getName() . " does not match"
                );
            }
        }

        return $output;
    }
}
