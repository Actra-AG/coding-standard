<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   MIT
 */

declare(strict_types=1);

namespace actra\codingStandard\phpstan;

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
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeCombinator;

/**
 * Finds the function, method or constructor a call refers to, for the own rules about arguments. Returns null for
 * calls PHPStan cannot resolve.
 */
final readonly class CalleeFinder
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {}

    public function find(CallLike $node, Scope $scope): FunctionReflection|ExtendedMethodReflection|null
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

    /**
     * Name of the callee for messages: `str_pad()`, `Mailer::send()`
     */
    public function describe(FunctionReflection|ExtendedMethodReflection $callee): string
    {
        if ($callee instanceof FunctionReflection) {
            return $callee->getName() . '()';
        }

        return $callee->getDeclaringClass()->getName() . '::' . $callee->getName() . '()';
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
}
