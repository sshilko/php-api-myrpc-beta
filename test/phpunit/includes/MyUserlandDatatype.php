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

use myrpc\Datatype\Internal\AbstractDatatype as InternalDatatype;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class MyUserlandDatatype extends InternalDatatype {
    //TODO define&fix arrays approach in serializing -->
    //public array $bookRatings;
    //public array $recentSalesDates;
    //TODO define&fix arrays approach in serializing <--

    #[
        Assert\NotBlank(null, "author name cant be blank"),
        Assert\Length(
        min: 0,
        max: 8,
        minMessage: 'Your A first name must be at least {{ limit }} characters long',
        maxMessage: 'Your B first name cannot be longer than {{ limit }} characters'),
    ]
    public string $authorName;

    /**
     * All properties must be defined in constructor to property cross-operate with Symfony/Serializer
     * MyRPC has no such limitation, it is Symfony/Serializer AbstractNormalizer::REQUIRE_ALL_PROPERTIES logic
     */
    public function __construct(
        string $authorName,
        public readonly int $authorAge,
        public bool $verified,
        public ?float $bookPrice = null)
    {
        $this->authorName = $authorName;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('authorAge', new Assert\GreaterThan(17, null, 'too low'));
    }
}
