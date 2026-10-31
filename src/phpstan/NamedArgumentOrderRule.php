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
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\TypeCombinator;

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
        private ReflectionProvider $reflectionProvider,
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
        $callee = $this->findCallee(node: $node, scope: $scope);
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
                $this->describe(callee: $callee),
                implode(separator: ', ', array: array_keys(array: $given)),
                implode(separator: ', ', array: array_keys(array: $expected)),
            ))
                ->identifier(identifier: 'actra.namedArgumentOrder')
                ->build(),
        ];
    }

    private function findCallee(CallLike $node, Scope $scope): FunctionReflection|ExtendedMethodReflection|null
    {
        return match (true) {
            $node instanceof FuncCall => $this->findFunction(node: $node, scope: $scope),
            // PHPStan also passes `$a?->b()` as MethodCall, so NullsafeMethodCall is skipped
            $node instanceof MethodCall => $this->findMethod(node: $node, scope: $scope),
            $node instanceof StaticCall => $this->findStaticMethod(node: $node, scope: $scope),
            $node instanceof New_ => $this->findConstructor(node: $node, scope: $scope),
            default => null,
        };
    }

    private function findFunction(FuncCall $node, Scope $scope): ?FunctionReflection
    {
        if (
            !$node->name instanceof Name
            || !$this->reflectionProvider->hasFunction(nameNode: $node->name, namespaceAnswerer: $scope)
        ) {
            return null;
        }

        return $this->reflectionProvider->getFunction(nameNode: $node->name, namespaceAnswerer: $scope);
    }

    private function findMethod(MethodCall $node, Scope $scope): ?ExtendedMethodReflection
    {
        if (!$node->name instanceof Identifier) {
            return null;
        }

        return $scope->getMethodReflection(
            typeWithMethod: TypeCombinator::removeNull(type: $scope->getType(node: $node->var)),
            methodName: $node->name->toString(),
        );
    }

    private function findStaticMethod(StaticCall $node, Scope $scope): ?ExtendedMethodReflection
    {
        if (!$node->name instanceof Identifier) {
            return null;
        }
        $type = $node->class instanceof Name
            ? $scope->resolveTypeByName(name: $node->class)
            : $scope->getType(node: $node->class)->getObjectTypeOrClassStringObjectType();

        return $scope->getMethodReflection(typeWithMethod: $type, methodName: $node->name->toString());
    }

    private function findConstructor(New_ $node, Scope $scope): ?ExtendedMethodReflection
    {
        if (!$node->class instanceof Name) {
            return null;
        }
        $className = $scope->resolveName(name: $node->class);
        if (!$this->reflectionProvider->hasClass(className: $className)) {
            return null;
        }
        $class = $this->reflectionProvider->getClass(className: $className);

        return $class->hasConstructor() ? $class->getConstructor() : null;
    }

    private function describe(FunctionReflection|ExtendedMethodReflection $callee): string
    {
        if ($callee instanceof FunctionReflection) {
            return $callee->getName() . '()';
        }

        return $callee->getDeclaringClass()->getName() . '::' . $callee->getName() . '()';
    }
}
