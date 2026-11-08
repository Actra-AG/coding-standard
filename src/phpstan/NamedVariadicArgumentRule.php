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
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Built-in functions and methods do not accept named arguments for their variadic parameter (see standards/php.md,
 * section 4): `array_map(callback: $f, array: $a, arrays: $b)` throws "ArgumentCountError: array_map() does not accept
 * unknown named parameters" at runtime, and PHPStan does not report it. Reports every named argument of a call to a
 * built-in callee with a variadic parameter that names none of its other parameters. Built-ins that forward named
 * arguments to another callable are skipped. Remove the rule once PHPStan reports these calls itself
 * (https://github.com/phpstan/phpstan/issues/14408).
 *
 * @implements Rule<CallLike>
 */
final readonly class NamedVariadicArgumentRule implements Rule
{
    /**
     * Built-ins that pass named arguments on to the called function or constructor (checked with PHP 8.5, see
     * https://github.com/phpstan/phpstan/issues/14408)
     */
    private const array FORWARDING_CALLEES = [
        'call_user_func()',
        'Closure::__invoke()',
        'Closure::call()',
        'Fiber::start()',
        'ReflectionClass::newInstance()',
        'ReflectionFunction::invoke()',
        'ReflectionMethod::invoke()',
    ];

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
        $namedArgs = [];
        foreach ($node->getArgs() as $arg) {
            if ($arg->name !== null) {
                $namedArgs[] = $arg->name->toString();
            }
        }
        if ($namedArgs === []) {
            return [];
        }
        $callee = $this->calleeFinder->find(node: $node, scope: $scope);
        if ($callee === null || !$this->isBuiltin(callee: $callee)) {
            return [];
        }
        $description = $this->calleeFinder->describe(callee: $callee);
        if (in_array(needle: $description, haystack: NamedVariadicArgumentRule::FORWARDING_CALLEES, strict: true)) {
            return [];
        }

        // PHP's own parameters: PHPStan's signatures of built-ins may differ (`max(mixed ...$value)`)
        $parameterNames = [];
        $variadicName = null;
        foreach ($this->nativeParameters(callee: $callee) as $parameter) {
            if ($parameter->isVariadic()) {
                $variadicName = $parameter->getName();
            } else {
                $parameterNames[$parameter->getName()] = true;
            }
        }
        if ($variadicName === null) {
            return [];
        }

        $errors = [];
        foreach ($namedArgs as $name) {
            if (array_key_exists(key: $name, array: $parameterNames)) {
                continue;
            }
            $errors[] = RuleErrorBuilder::message(message: sprintf(
                'Named argument %s: of built-in %s is collected by its variadic parameter $%s, which throws an '
                . 'ArgumentCountError at runtime. Pass these arguments positionally.',
                $name,
                $description,
                $variadicName,
            ))
                ->identifier(identifier: 'actra.namedVariadicArgument')
                ->build();
        }

        return $errors;
    }

    private function isBuiltin(FunctionReflection|ExtendedMethodReflection $callee): bool
    {
        if ($callee instanceof FunctionReflection) {
            return $callee->isBuiltin();
        }

        return $callee->getDeclaringClass()->isBuiltin();
    }

    /**
     * Parameters of the built-in in the PHP that runs PHPStan; none if its extension is not loaded there or PHP has
     * no reflection of it (magic methods). Runtime reflection is safe for built-ins, which are loaded anyway, unlike
     * the analysed code.
     *
     * @return list<ReflectionParameter>
     */
    private function nativeParameters(FunctionReflection|ExtendedMethodReflection $callee): array
    {
        try {
            if ($callee instanceof FunctionReflection) {
                // @phpstan-ignore phpstanApi.runtimeReflection (built-in function, see above)
                return new ReflectionFunction(function: $callee->getName())->getParameters();
            }

            // @phpstan-ignore phpstanApi.runtimeReflection (method of a built-in class, see above)
            return new ReflectionMethod(
                objectOrMethod: $callee->getDeclaringClass()->getName(),
                method: $callee->getName(),
            )->getParameters();
        } catch (ReflectionException) {
            return [];
        }
    }
}
