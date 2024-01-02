<?php

declare(strict_types = 1);

namespace myrpc\Validator;

use myrpc\Exception\ValidatorException;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validation;
use Throwable;
use function count;

/**
 * Symfony validator handles user types validation on input
 * - supports attributes constraints
 * - supports classMetadata
 */
class Symfony implements ValidatorInterface
{

    protected readonly \Symfony\Component\Validator\Validator\ValidatorInterface $validator;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct()
    {
        $builder = Validation::createValidatorBuilder();
        $builder->enableAttributeMapping();
        $builder->addLoader(new StaticMethodLoader());
        $this->validator = $builder->getValidator();
    }

    /**
     * @return array<\myrpc\Validator\ValidatorResultInterface>
     * @throws \myrpc\Exception\ValidatorException
     */
    public function validate(IsValidatableInterface $input): array
    {
        $response = [];
        try {
            $constraintViolationList = $this->validator->validate($input->validatableObject());
            if (count($constraintViolationList) > 0) {
                foreach ($constraintViolationList as $r) {
                    $response[] = new SymfonyResult($r->getPropertyPath(), (string) $r->getCode(), $r->getMessage());
                }
            }
        } catch (Throwable $e) {
            throw new ValidatorException("Validator exception: " . $e->getMessage(), (int) $e->getCode(), $e);
        }

        return $response;
    }
}
