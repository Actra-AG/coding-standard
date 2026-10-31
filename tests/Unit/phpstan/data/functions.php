<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   MIT
 */

declare(strict_types=1);

namespace actra\codingStandard\tests\Unit\phpstan\data;

// Fixture of NamedArgumentOrderRuleTest, autoloaded by autoload-dev in composer.json

function format(string $text, int $width = 80, string $break = "\n"): string
{
    return wordwrap(string: $text, width: $width, break: $break);
}
