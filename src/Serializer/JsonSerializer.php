<?php

declare(strict_types = 1);

namespace myrpc\Serializer;

use myrpc\Exception\SerializerException;
use myrpc\Exception\SerializerObjectAssertException;
use stdClass;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Serializer;
use Throwable;
use function is_object;
use const JSON_THROW_ON_ERROR;

/**
 * JsonSerializer deals with object (un)wrapping, marshalling, protocol representation <> business layer
 * @see https://symfony.com/doc/current/components/serializer.html#recursive-denormalization-and-type-safety
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JsonSerializer implements SerializerInterface
{
    private const FORMAT = JsonEncoder::FORMAT;
    private const RECURSION_DEPTH = 64;

    protected readonly Serializer $serializer;

    public function __construct()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());

        /**
         *
         * @see https://symfony.com/doc/6.4/components/serializer.html#normalizers
         */
        $normalizers = [
            new ObjectNormalizer($classMetadataFactory, null, null, new ReflectionExtractor()),
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            new BackedEnumNormalizer(),
            new DataUriNormalizer(),
            new DateTimeZoneNormalizer(),
            new UidNormalizer(),
            new PropertyNormalizer(),
            new JsonSerializableNormalizer(),
        ];

        $this->serializer = new Serializer($normalizers, [new JsonEncoder()]);
    }

    /**
     * Safely un-serialize any class
     * NO support for interfaces
     * No support for abstract classes
     */
    public function denormalize(stdClass $input, string $className): object
    {
        /**
         * By default, additional attributes that are not mapped to the denormalized
         * object will be ignored by the Serializer component.
         * All accessible attributes are included by default when serializing objects.
         * @see https://symfony.com/doc/current/components/serializer.html#the-jsonencoder
         */
        $options = [

            AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,

            // TODO: document behaviour
            // AstractNormalizer::REQUIRE_ALL_PROPERTIES only works when doing constructor property promotion 8.0 style
            AbstractNormalizer::REQUIRE_ALL_PROPERTIES => true,
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => false,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => false,
            AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => false,
            BackedEnumNormalizer::ALLOW_INVALID_VALUES => false,
            JsonDecode::ASSOCIATIVE => false,
            JsonDecode::OPTIONS => JSON_THROW_ON_ERROR,
            JsonDecode::RECURSION_DEPTH => self::RECURSION_DEPTH,
            JsonEncode::OPTIONS => JSON_THROW_ON_ERROR,
            PropertyNormalizer::NORMALIZE_PUBLIC => PropertyNormalizer::NORMALIZE_PUBLIC,
            UidNormalizer::NORMALIZATION_FORMAT_KEY => UidNormalizer::NORMALIZATION_FORMAT_RFC4122,
        ];

        try {
            /**
             * TODO:
             * It is possible to denormalize array of types
             * example: array of "Book" classes with className post-fixed [], as "Book[]"
             * decision is to NOT support this use-case, as it brings complexity
             * instead top-level API arguments should be defined as businessSpecific types, not generic arrays
             */
            $result = $this->serializer->denormalize($input, $className, self::FORMAT, $options);
            if (!is_object($result)) {
                throw new SerializerObjectAssertException("Only support object serialization");
            }

            return $result;
        } catch (Throwable $e) {
            throw new SerializerException($e->getMessage(), 0, $e);
        }
    }
}
