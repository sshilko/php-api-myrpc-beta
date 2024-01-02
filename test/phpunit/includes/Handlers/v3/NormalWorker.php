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

namespace phpunit\includes\Handlers\v3;

use myrpc\Datatype\DatatypeInterface;
use myrpc\Datatype\Internal\v1\Response\Success;
use myrpc\Handler\Worker\WorkerInterface;
use myrpc\Handler\Worker\WorkerTrait;
use phpunit\includes\MyBackendEnumHTTTPMethods;
use phpunit\includes\MyPlainEnum;
use phpunit\includes\MyUserlandDatatype;
use stdClass;

/**
 * @author Sergei Shilko <contact@sshilko.com>
 * @license https://opensource.org/licenses/mit-license.php MIT
 *
 * @see https://github.com/sshilko/php-api-myrpc
 */
class NormalWorker implements WorkerInterface
{

    use WorkerTrait;

    public function inputNull(?string $input): ?string
    {
        return $input;
    }

    public function inputArray(array $myPlainArray): array
    {
        return $myPlainArray;
    }

    public function inputAnyStringExceptBerlin(string $string): string
    {
        return $string;
    }

    public function inputBool(bool $bool): bool
    {
        return $bool;
    }

    public function inputString(string $string): string
    {
        return $string;
    }

    public function inputStringBetween1And5Characters(string $string): string
    {
        return $string;
    }

    public function inputDate(string $string): string
    {
        return $string;
    }

    public function inputUUIDorEmail(string $string): string
    {
        return $string;
    }

    public function input5or6(int $int): int
    {
        return $int;
    }

    public function inputIntOrString(int | string $myIntOrStringInput): int | string
    {
        return $myIntOrStringInput;
    }

    public function inputInt(int $int): int
    {
        return $int;
    }

    public function inputFloat(float $float): float
    {
        return $float;
    }

    /**
     * This is not supported, use backend enums only
     */
    public function acceptPlainEnumArguments(MyPlainEnum $plainEnum1): MyPlainEnum
    {
        return $plainEnum1;
    }

    /**
     * Allow native php enum argument injection
     */
    public function acceptBackendEnumArguments(MyBackendEnumHTTTPMethods $backedEnum1): MyBackendEnumHTTTPMethods
    {
        return $backedEnum1::from("post");
    }

    /**
     * Accept & return user defined data-types
     */
    public function getMyUserlandDatatype(MyUserlandDatatype $book): MyUserlandDatatype
    {
        $book->verified = true;

        return $book;
    }

    public function inputNamedArguments(
        string $mystring1,
        int $myint2,
        bool $mybool3,
        float $myfloat4
    ): DatatypeInterface {
        $response = new stdClass();
        $response->mystring1 = $mystring1;
        $response->myint2 = $myint2;
        $response->mybool3 = $mybool3;
        $response->myfloat4 = $myfloat4;

        return new Success($response);
    }
}
