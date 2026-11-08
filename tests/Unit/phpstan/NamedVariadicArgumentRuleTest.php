<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   MIT
 */

declare(strict_types=1);

namespace actra\codingStandard\tests\Unit\phpstan;

use actra\codingStandard\phpstan\CalleeFinder;
use actra\codingStandard\phpstan\NamedVariadicArgumentRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NamedVariadicArgumentRule>
 */
final class NamedVariadicArgumentRuleTest extends RuleTestCase
{
    #[Override]
    protected function getRule(): Rule
    {
        return new NamedVariadicArgumentRule(
            calleeFinder: new CalleeFinder(reflectionProvider: $this->createReflectionProvider()),
        );
    }

    public function testReportsNamedArgumentsForTheVariadicParameterOfBuiltins(): void
    {
        $message = 'Named argument %s: of built-in %s is collected by its variadic parameter $%s, which throws an '
            . 'ArgumentCountError at runtime. Pass these arguments positionally.';

        $this->analyse(files: [__DIR__ . '/data/named-variadic-argument.php'], expectedErrors: [
            [sprintf($message, 'arrays', 'array_map()', 'arrays'), 32],
            [sprintf($message, 'values', 'array_push()', 'values'), 33],
            [sprintf($message, 'values', 'sprintf()', 'values'), 34],
            [sprintf($message, 'values', 'max()', 'values'), 35],
            [sprintf($message, 'nodes', 'DOMElement::append()', 'nodes'), 36],
            [sprintf($message, 'arrays', 'array_merge()', 'arrays'), 37],
        ]);
    }
}
