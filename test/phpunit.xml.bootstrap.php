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

include_once 'vendor/autoload.php';

/**
 * This is only necessary if autoloader does not work
 * @deprecated
 */
foreach (glob(__DIR__ . '/phpunit/includes/*.php') as $filename) {
    include_once $filename;
}
