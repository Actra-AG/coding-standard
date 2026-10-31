<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   MIT
 */

declare(strict_types=1);

namespace actra\codingStandard\tests\Unit\phpstan;

use actra\codingStandard\phpstan\NamedArgumentOrderRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NamedArgumentOrderRule>
 */
final class NamedArgumentOrderRuleTest extends RuleTestCase
{
    private const string MAILER = 'actra\codingStandard\tests\Unit\phpstan\data\Mailer';
    private const string FORMAT = 'actra\codingStandard\tests\Unit\phpstan\data\format()';

    #[Override]
    protected function getRule(): Rule
    {
        return new NamedArgumentOrderRule(reflectionProvider: $this->createReflectionProvider());
    }

    public function testReportsNamedArgumentsNotInParameterOrder(): void
    {
        $message = 'Named arguments of %s are not in the order of its parameters: given %s, expected %s.';

        $this->analyse(files: [__DIR__ . '/data/named-argument-order.php'], expectedErrors: [
            [sprintf($message, NamedArgumentOrderRuleTest::MAILER . '::send()', 'subject, to', 'to, subject'), 26],
            [sprintf($message, NamedArgumentOrderRuleTest::MAILER . '::send()', 'body, subject', 'subject, body'), 27],
            [sprintf($message, NamedArgumentOrderRuleTest::MAILER . '::send()', 'body, to', 'to, body'), 28],
            [sprintf($message, NamedArgumentOrderRuleTest::MAILER . '::create()', 'tls, host', 'host, tls'), 29],
            [sprintf($message, NamedArgumentOrderRuleTest::MAILER . '::__construct()', 'tls, host', 'host, tls'), 30],
            [sprintf($message, NamedArgumentOrderRuleTest::FORMAT, 'width, text', 'text, width'), 34],
            [sprintf($message, 'str_pad()', 'length, string', 'string, length'), 35],
        ]);
    }
}
