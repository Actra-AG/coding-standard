<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   MIT
 */

declare(strict_types=1);

// Fixture of NamedVariadicArgumentRuleTest, with deliberately failing calls (excluded in phpstan.neon)

namespace actra\codingStandard\tests\Unit\phpstan\data;

use Closure;
use DOMElement;
use ReflectionClass;

/**
 * @param list<int> $values
 */
function variadicExamples(Mailer $mailer, DOMElement $element, Closure $callback, array $values): void
{
    // Named arguments before the variadic parameter, positional or spread variadic arguments
    array_map(callback: $callback, array: $values);
    array_map($callback, $values, $values);
    array_push($values, ...$values);
    sprintf('%d', 1);
    max(value: $values);
    $element->append('a');

    // Named argument for the variadic parameter of a built-in
    array_map(callback: $callback, array: $values, arrays: $values);
    array_push(array: $values, values: 1);
    sprintf(format: '%d', values: 1);
    max(value: 1, values: 2);
    $element->append(nodes: 'a');
    array_merge(arrays: $values);

    // Skipped: own variadic functions collect named arguments, built-ins that forward them, unresolvable calls
    $mailer->log(level: 'info', user: 'a');
    call_user_func($callback, user: 'a');
    $callback->__invoke(user: 'a');
    new ReflectionClass(objectOrClass: Mailer::class)->newInstance(host: 'a');
    unknownFunction(values: 1);
}
