<?php

declare(strict_types=1);

namespace Highstan\Higher\Reflection;

use Highstan\Higher\TypeLam;
use Highstan\Higher\TypeLamApp;
use Highstan\Higher\TypeLamParam;
use Highstan\Higher\TypeLamParamVariance;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final readonly class TypeLamAppRepresentation
{
    /**
     * @param non-negative-int $expectedParamsCount
     */
    public function __construct(
        public int $expectedParamsCount,
    ) {}

    public function forArray(Type $type): ?TypeLamApp
    {
        $isGenericArray = $type->isArray()->yes() && $type->isConstantArray()->no();

        if (!$isGenericArray) {
            return null;
        }

        $valueParam = new TypeLamParam(
            name: 'v',
            upperBound: new MixedType(),
            variance: TypeLamParamVariance::Covariant,
        );

        if ($this->expectedParamsCount === 1) {
            return new TypeLamApp(
                typeLam: new TypeLam(
                    params: [$valueParam],
                    return: new ArrayType($type->getIterableKeyType(), $valueParam),
                ),
                params: [$type->getIterableValueType()],
            );
        }

        $keyParam = new TypeLamParam(
            name: 'k',
            upperBound: TypeCombinator::union(
                new IntegerType(),
                new StringType(),
            ),
            variance: TypeLamParamVariance::Covariant,
        );

        return new TypeLamApp(
            typeLam: new TypeLam(
                params: [$keyParam, $valueParam],
                return: new ArrayType($keyParam, $valueParam),
            ),
            params: [$type->getIterableKeyType(), $type->getIterableValueType()],
        );
    }

    public function forList(Type $type): ?TypeLamApp
    {
        $isGenericList = $type->isList()->yes() && $type->isConstantArray()->no();

        if (!$isGenericList) {
            return null;
        }

        $listParam = new TypeLamParam(
            name: 'v',
            upperBound: new MixedType(),
            variance: TypeLamParamVariance::Covariant,
        );

        return new TypeLamApp(
            typeLam: new TypeLam(
                params: [$listParam],
                return: TypeCombinator::intersect(
                    new ArrayType(new IntegerType(), $listParam),
                    new AccessoryArrayListType(),
                ),
            ),
            params: [$type->getIterableValueType()],
        );
    }

    public function forObject(Type $type): ?TypeLamApp
    {
        if (!$type->isObject()->yes()) {
            return null;
        }

        $objectReflections = $type->getObjectClassReflections();

        return \count($objectReflections) === 1
            ? self::doForObject($objectReflections[0])
            : null;
    }

    public function forTypeLamApp(Type $type): ?TypeLamApp
    {
        if (!$type instanceof TypeLamApp) {
            return null;
        }

        $allInputParams = $type->getParams();
        $allInputParamsCount = \count($allInputParams);

        if ($allInputParamsCount === $this->expectedParamsCount) {
            return null;
        }

        $notPartiallyApplied = array_map(
            static fn(TypeLamParam $p) => new TypeLamParam(
                name: "g_{$p->name}",
                upperBound: $p->upperBound,
                variance: $p->variance,
            ),
            \array_slice(
                array: $type->getTypeLamBound()->params,
                offset: -$this->expectedParamsCount,
            ),
        );

        if ($notPartiallyApplied === []) {
            return null;
        }

        $partiallyApplied = \array_slice(
            array: $allInputParams,
            offset: 0,
            length: $allInputParamsCount - $this->expectedParamsCount,
        );

        if ($partiallyApplied === []) {
            return null;
        }

        $partiallyAppliedTypeLam = new TypeLamApp(
            typeLam: $type->getTypeLam(),
            params: [...$partiallyApplied, ...$notPartiallyApplied],
        );

        $typeLamWithPartiallyAppliedReturn = new TypeLam(
            params: $notPartiallyApplied,
            return: $partiallyAppliedTypeLam,
        );

        $appliedParams = \array_slice(
            array: $allInputParams,
            offset: -$this->expectedParamsCount,
            length: $this->expectedParamsCount,
        );

        return $appliedParams !== []
            ? new TypeLamApp($typeLamWithPartiallyAppliedReturn, $appliedParams)
            : null;
    }

    private function doForObject(ClassReflection $classReflection): ?TypeLamApp
    {
        $bestKind = (new BestKindFinder($this->expectedParamsCount))->find($classReflection);

        if ($bestKind === null) {
            return null;
        }

        $notPartiallyApplied = \array_slice(
            array: $bestKind->templates,
            offset: $bestKind->leftParamsCount,
        );

        if ($notPartiallyApplied === []) {
            return null;
        }

        $notPartiallyAppliedParams = array_map(
            static fn(array $template) => new TypeLamParam(
                name: strtolower($template['name']),
                upperBound: $template['upperBound'],
                variance: $template['variance'],
            ),
            $notPartiallyApplied,
        );

        $partiallyAppliedParams = \array_slice(
            array: array_column($bestKind->templates, 'typeArgument'),
            offset: 0,
            length: $bestKind->leftParamsCount,
        );

        $typeLamReturn = new GenericObjectType($bestKind->typename, [...$partiallyAppliedParams, ...$notPartiallyAppliedParams]);
        $coercedTypeLam = new TypeLam($notPartiallyAppliedParams, $typeLamReturn);

        $appliedParams = \array_slice(
            array: array_column($bestKind->templates, 'typeArgument'),
            offset: $bestKind->leftParamsCount,
        );

        return $appliedParams !== []
            ? new TypeLamApp($coercedTypeLam, $appliedParams)
            : null;
    }
}
