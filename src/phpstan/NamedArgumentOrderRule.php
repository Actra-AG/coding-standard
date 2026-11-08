<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   MIT
 */

declare(strict_types=1);

namespace actra\codingStandard\phpstan;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Named arguments follow the parameter order of the called function (see standards/php.md, section 4). PHP accepts any
 * order; readers and reviewers expect the declared one. Calls PHPStan cannot resolve, calls with spread arguments and
 * named arguments collected by a variadic parameter are skipped; positional arguments come first anyway.
 *
 * @implements Rule<CallLike>
 */
final readonly class NamedArgumentOrderRule implements Rule
{
    public function __construct(
        private CalleeFinder $calleeFinder,
    ) {}

    #[Override]
    public function getNodeType(): string
    {
        return CallLike::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->isFirstClassCallable()) {
            return [];
        }
        $args = $node->getArgs();
        $namedArgs = [];
        foreach ($args as $arg) {
            if ($arg->unpack) {
                return [];
            }
            if ($arg->name !== null) {
                $namedArgs[] = $arg->name->toString();
            }
        }
        if (count(value: $namedArgs) < 2) {
            return [];
        }
        $callee = $this->calleeFinder->find(node: $node, scope: $scope);
        if ($callee === null) {
            return [];
        }

        $parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
            scope: $scope,
            args: $args,
            parametersAcceptors: $callee->getVariants(),
            namedArgumentsVariants: $callee->getNamedArgumentsVariants(),
        );
        $positions = [];
        foreach ($parametersAcceptor->getParameters() as $position => $parameter) {
            if (!$parameter->isVariadic()) {
                $positions[$parameter->getName()] = $position;
            }
        }
        // Position of each named argument in the given order; names without a declared parameter go to a variadic
        // parameter or are reported by PHPStan itself
        $given = [];
        foreach ($namedArgs as $name) {
            if (array_key_exists(key: $name, array: $positions)) {
                $given[$name] = $positions[$name];
            }
        }
        $expected = $given;
        asort(array: $expected);
        if ($given === $expected) {
            return [];
        }

        return [
            RuleErrorBuilder::message(message: sprintf(
                'Named arguments of %s are not in the order of its parameters: given %s, expected %s.',
                $this->calleeFinder->describe(callee: $callee),
                implode(separator: ', ', array: array_keys(array: $given)),
                implode(separator: ', ', array: array_keys(array: $expected)),
            ))
                ->identifier(identifier: 'actra.namedArgumentOrder')
                ->build(),
        ];
    }
}
