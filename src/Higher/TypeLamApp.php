<?php

declare(strict_types=1);

namespace Highstan\Higher;

use Highstan\Higher\Reflection\TypeLamAppRepresentation;
use Highstan\Higher\Reflection\TypeLamEvaluator;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\TrinaryLogic;
use PHPStan\Type\AcceptsResult;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Generic\TemplateTypeVariance;
use PHPStan\Type\IsSuperTypeOfResult;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class TypeLamApp extends ObjectWithoutClassType
{
    use NoSubtractionTrait;

    /**
     * @param non-empty-list<Type> $params
     */
    public function __construct(
        private readonly Type $typeLam,
        private readonly array $params,
    ) {
        parent::__construct();

        if ($typeLam instanceof TemplateType && $typeLam->getBound() instanceof WrappedTypeLam) {
            return;
        }

        if ($typeLam instanceof TypeLam) {
            return;
        }

        throw new ShouldNotHappenException();
    }

    /**
     * @return non-empty-list<Type>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function getTypeLam(): Type
    {
        return $this->typeLam;
    }

    public function getTypeLamBound(): TypeLam
    {
        if ($this->typeLam instanceof TemplateType) {
            $bound = $this->typeLam->getBound();

            return $bound instanceof WrappedTypeLam
                ? $bound->unwrap()
                : throw new ShouldNotHappenException();
        }

        return $this->typeLam instanceof TypeLam
            ? $this->typeLam
            : throw new ShouldNotHappenException();
    }

    public function traverse(callable $cb): Type
    {
        $changed = false;

        $traversedTypeLam = $cb($this->typeLam);

        if ($traversedTypeLam !== $this->typeLam) {
            // Hack to prevent early TypeLamApp evaluation to the return of type type-lam.
            // See also TypeLamApp::isSuperTypeOfTypeLam method.
            if ($this->typeLam instanceof TemplateType && $this->typeLam->getBound() === $traversedTypeLam) {
                $changed = false;
                $traversedTypeLam = $this->typeLam;
            } else {
                $changed = true;
            }
        }

        $traversedParams = [];

        foreach ($this->params as $param) {
            $traversedParam = $cb($param);

            if ($traversedParam !== $param) {
                $changed = true;
            }

            $traversedParams[] = $traversedParam;
        }

        return $changed
            ? TypeLamEvaluator::tryToCompute($traversedTypeLam, $traversedParams)
            : $this;
    }

    public function hasMethod(string $methodName): TrinaryLogic
    {
        return TypeLamEvaluator::tryToCompute($this->typeLam, $this->params, withTemplateType: true)
            ->hasMethod($methodName);
    }

    public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): ExtendedMethodReflection
    {
        return TypeLamEvaluator::tryToCompute($this->typeLam, $this->params, withTemplateType: true)
            ->getMethod($methodName, $scope);
    }

    public function hasProperty(string $propertyName): TrinaryLogic
    {
        return TypeLamEvaluator::tryToCompute($this->typeLam, $this->params, withTemplateType: true)
            ->hasProperty($propertyName);
    }

    public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): ExtendedPropertyReflection
    {
        return TypeLamEvaluator::tryToCompute($this->typeLam, $this->params, withTemplateType: true)
            ->getProperty($propertyName, $scope);
    }

    public function inferTemplateTypes(Type $receivedType): TemplateTypeMap
    {
        $typeLamRepr = new TypeLamAppRepresentation(
            expectedParamsCount: \count($this->getTypeLamBound()->params),
        );

        $receivedTypeAsTypeLamApp = $typeLamRepr->forObject($receivedType)
            ?? $typeLamRepr->forList($receivedType)
            ?? $typeLamRepr->forArray($receivedType)
            ?? $typeLamRepr->forTypeLamApp($receivedType)
            ?? $receivedType;

        if (!$receivedTypeAsTypeLamApp instanceof self) {
            return new TemplateTypeMap(
                $this->typeLam instanceof TemplateType
                    ? [$this->typeLam->getName() => UnevaluatedTypeLam::isNotSubtype($this, $receivedTypeAsTypeLamApp)]
                    : [],
            );
        }

        $thisParamsCount = \count($this->params);
        $thatParamsCount = \count($receivedTypeAsTypeLamApp->params);

        if ($thisParamsCount !== $thatParamsCount) {
            return new TemplateTypeMap(
                $this->typeLam instanceof TemplateType
                    ? [$this->typeLam->getName() => UnevaluatedTypeLam::isNotSubtype($this, $receivedTypeAsTypeLamApp)]
                    : [],
            );
        }

        $typeMap = [];

        if ($this->typeLam instanceof TemplateType) {
            $thisParamBound = $this->getTypeLamBound();
            $thatParamType = $receivedTypeAsTypeLamApp->getTypeLamBound();
            $isTypeLamsCompatible = $thisParamBound->isSuperTypeOf($thatParamType);

            $inferredTypeLam = $isTypeLamsCompatible->yes()
                ? $receivedTypeAsTypeLamApp->typeLam
                : UnevaluatedTypeLam::cannotSubstitute($thisParamBound, $thatParamType, $isTypeLamsCompatible->reasons);

            $typeMap[$this->typeLam->getName()] = $inferredTypeLam;
        }

        $templateTypeMap = new TemplateTypeMap($typeMap);

        foreach ($this->params as $i => $thisParamType) {
            $templateTypeMap = $templateTypeMap->union(
                $thisParamType->inferTemplateTypes($receivedTypeAsTypeLamApp->params[$i]),
            );
        }

        return $templateTypeMap;
    }

    public function getReferencedTemplateTypes(TemplateTypeVariance $positionVariance): array
    {
        $references = [];

        foreach ($this->getTypeLamBound()->params as $i => $typeLamParam) {
            if (!\array_key_exists($i, $this->params)) {
                continue;
            }

            $variance = $positionVariance->compose($typeLamParam->variance->toClassTemplateTypeVariance());

            foreach ($this->params[$i]->getReferencedTemplateTypes($variance) as $reference) {
                $references[] = $reference;
            }
        }

        foreach ($this->typeLam->getReferencedTemplateTypes($positionVariance) as $reference) {
            $references[] = $reference;
        }

        return $references;
    }

    public function equals(Type $type): bool
    {
        return $this->isSuperTypeOf($type)->yes()
            && $type->isSuperTypeOf($this)->yes();
    }

    private static function isSuperTypeOfTypeLam(Type $parentTypeLam, Type $derivedTypeLam): IsSuperTypeOfResult
    {
        // interface Base
        // {
        //     /**
        //      * @template F of type-lam<_>
        //      * @param F<int> $fa
        //      * @return F<int>
        //      */
        //     public function m(mixed $fa): mixed;
        // }
        //
        // interface Derived extends Base
        // {
        //     /**
        //      * Question:
        //      * - Is Derived::G<int> in the @param tag considered a supertype of Base::F<int>?
        //      * - Is Derived::G<int> in the @return tag considered a subtype of Base::F<int>?
        //      *
        //      * Current Behavior in PHPStan:
        //      * - In order to perform this check, PHPStan resolves type parameters to their upper bound.
        //      * - As a result, both F<int> and G<int> are reduced to mixed.
        //      * - This happens because such types are simplified to
        //      *   (type-lam<x of mixed>(x): mixed)<int>, which is then collapsed
        //      *   into mixed (the return type of the type-lam).
        //      * - Regardless of the type arguments provided to the type-lam,
        //      *   PHPStan simplifies the result to the return type of the type-lam.
        //      *
        //      * Workaround:
        //      * - PHPStan no longer reduces a type-lam application to its return type.
        //      *   (see hack in the TypeLamApp::traverse)
        //      * - To correctly compare two template types (e.g., Derived::G<int> and Base::F<int>),
        //      *   it is now necessary to rely on $isTemplateFromParentMethod.
        //      *
        //      * @template G of type-lam<_>
        //      * @param G<int> $fa
        //      * @return G<int>
        //      */
        //     public function m(mixed $fa): mixed
        // }
        $isTemplateFromParentMethod = static function (TemplateType $parent, TemplateType $derived): bool {
            if (!$parent->getBound() instanceof WrappedTypeLam || !$derived->getBound() instanceof WrappedTypeLam) {
                return false;
            }

            $parentScope = $parent->getScope();
            $parentClass = $parentScope->getClassName();
            $parentMethod = $parentScope->getFunctionName();

            if ($parentClass === null || $parentMethod === null) {
                return false;
            }

            $derivedScope = $derived->getScope();
            $derivedClass = $derivedScope->getClassName();
            $derivedMethod = $derivedScope->getFunctionName();

            if ($derivedClass === null || $derivedMethod === null) {
                return false;
            }

            $parentType = new ObjectType($parentClass);
            $derivedType = new ObjectType($derivedClass);

            return $parentClass !== $derivedClass
                && ($parentType->isSuperTypeOf($derivedType)->yes() || $derivedType->isSuperTypeOf($parentType)->yes())
                && $parentMethod === $derivedMethod;
        };

        if ($parentTypeLam instanceof TemplateType
            && $derivedTypeLam instanceof TemplateType
            && $isTemplateFromParentMethod($parentTypeLam, $derivedTypeLam)
        ) {
            return $parentTypeLam->getBound()->isSuperTypeOf($derivedTypeLam->getBound());
        }

        return $parentTypeLam->isSuperTypeOf($derivedTypeLam);
    }

    public function isSuperTypeOf(Type $type): IsSuperTypeOfResult
    {
        if (!$type instanceof self) {
            return IsSuperTypeOfResult::createNo();
        }

        if (!self::isSuperTypeOfTypeLam($this->typeLam, $type->typeLam)->yes()) {
            return IsSuperTypeOfResult::createNo();
        }

        if (\count($this->params) !== \count($type->params)) {
            return IsSuperTypeOfResult::createNo();
        }

        $typeLam = $this->getTypeLamBound();
        $whyNotIsSuperType = [];

        foreach ($this->params as $i => $currentParam) {
            $inputParam = $type->params[$i];
            $variance = ($typeLam->params[$i] ?? null)->variance ?? TypeLamParamVariance::Invariant;

            if (!$variance->isValidVariance($inputParam, $currentParam)) {
                $whyNotIsSuperType[] = $variance->getInvalidVarianceMessage($inputParam, $currentParam);
            }
        }

        return $whyNotIsSuperType !== []
            ? IsSuperTypeOfResult::createNo($whyNotIsSuperType)
            : IsSuperTypeOfResult::createYes();
    }

    public function accepts(Type $type, bool $strictTypes): AcceptsResult
    {
        return $this->isSuperTypeOf($type)->toAcceptsResult();
    }

    public function describe(VerbosityLevel $level): string
    {
        return \sprintf(
            '%s<%s>%s',
            $this->typeLam instanceof TemplateType
                ? $this->typeLam->getName()
                : $this->typeLam->describe($level),
            implode(', ', array_map(
                static fn(Type $t) => $t->describe($level),
                $this->params,
            )),
            $this->typeLam instanceof TemplateType
                ? " of ({$this->typeLam->getBound()->describe($level)}) ({$this->typeLam->getScope()->describe()})"
                : '',
        );
    }
}
