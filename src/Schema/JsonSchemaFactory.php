<?php

declare(strict_types = 1);

namespace myrpc\Schema;

use myrpc\Datatype\DatatypeFactoryInterface;
use myrpc\Exception\ServiceException;
use function current;
use function gettype;
use function is_array;
use function ksort;
use function usort;

class JsonSchemaFactory implements SchemaFactoryInterface
{
    public function __construct(protected DatatypeFactoryInterface $datatypeFactory)
    {
    }

    /**
     * @throws ServiceException
     */
    public function newSchemaFromObject(object $obj): SchemaInterface
    {
        $schema = [];
        $definitions = [];

        //TODO RESUME HERE 01.04.2023, 23.04.2023
        //TODO needs type definitions use, see $this->datatypeFactory

        $schema = $this->sortSchema($schema);

        //ksort($definitions);
        $schema['obj'] = $obj; //TODO: remove me
        $schema['definitions'] = $definitions;

        return new JsonSchema($schema);
    }

    /**
     * @param array<array-key, array<array-key, array<array-key, mixed>>> $schema
     * @return array
     * @throws ServiceException
     */
    protected function sortSchema(array $schema): array
    {
        $schemaSorted = [];
        /* @phan-suppress-next-line PhanThrowTypeAbsent */
        $sortfunc = static function (array $a, array $b): int {
            if (!isset($a['title'], $b['title'])) {
                /* @phan-suppress-next-line PhanThrowTypeAbsent */
                throw new ServiceException('Unexpected JSON schema root item: ' . gettype($a) . ' & ' . gettype($b));
            }

            return $a['title'] <=> $b['title'];
        };
        foreach ($schema as $sk => $sv) {
            if (is_array(current($sv))) {
                usort($sv, $sortfunc);
            }
            $schemaSorted[$sk] = $sv;
        }

        return $schemaSorted;
    }

    protected function sortDefinitions(array $definitions): array
    {
        ksort($definitions);

        return $definitions;
    }
}
