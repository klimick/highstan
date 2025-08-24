<?php

declare(strict_types=1);

namespace Highstan\Higher\Rule;

use Highstan\Higher\TypeLamParam;
use Highstan\Higher\TypeLamParamVariance;
use PHPStan\Type\ArrayType;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeVariance;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\ObjectShapeType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;

final readonly class TypeLamParamRefCollector
{
    /**
     * @return list<TypeLamParamRef|TypeLamParamRefUnsupported>
     */
    public static function collect(
        Type $fromType,
        ?TypeLamParamVariance $positionVariance = null,
    ): array {
        /** @var list<TypeLamParamRef|TypeLamParamRefUnsupported> $references */
        $references = [];
        $unsupportedTypeDetected = false;

        TypeTraverser::map($fromType, static function (Type $t, callable $traverse) use (&$references, &$unsupportedTypeDetected, $positionVariance) {
            if ($t instanceof UnionType || $t instanceof IntersectionType) {
                return $traverse($t);
            }

            $collected = match ($t::class) {
                TypeLamParam::class => self::collectTypeLamParamRef($t, $positionVariance, $unsupportedTypeDetected),
                GenericObjectType::class => self::collectFromGenericObject($t, $positionVariance),
                ConstantArrayType::class => self::collectFromConstantArray($t, $positionVariance),
                ObjectShapeType::class => self::collectFromObjectShape($t, $positionVariance),
                ArrayType::class, IterableType::class => self::collectFromIterable($t, $positionVariance),
                ClosureType::class, CallableType::class => self::collectFromCallable($t, $positionVariance),
                default => null,
            };

            if ($collected !== null) {
                foreach ($collected as $reference) {
                    $references[] = $reference;
                }

                return $t;
            }

            $unsupportedTypeDetected = true;

            return $traverse($t);
        });

        return $references;
    }

    /**
     * @return \Generator<int, TypeLamParamRef|TypeLamParamRefUnsupported>
     */
    private static function collectTypeLamParamRef(
        TypeLamParam $typeLamParam,
        ?TypeLamParamVariance $positionVariance,
        bool $unsupportedTypeDetected,
    ): \Generator {
        yield $unsupportedTypeDetected
            ? new TypeLamParamRefUnsupported($typeLamParam)
            : new TypeLamParamRef($typeLamParam, $positionVariance ?? $typeLamParam->variance);
    }

    /**
     * @return \Generator<int, TypeLamParamRef|TypeLamParamRefUnsupported>
     */
    private static function collectFromGenericObject(
        GenericObjectType $genericObjectType,
        ?TypeLamParamVariance $positionVariance,
    ): \Generator {
        $callSiteVariances = $genericObjectType->getVariances();
        $classReflection = $genericObjectType->getClassReflection();

        $declarationSiteVariances = $classReflection !== null
            ? $classReflection->typeMapToList($classReflection->getTemplateTypeMap())
            : [];

        foreach ($genericObjectType->getTypes() as $i => $type) {
            $callSiteVariance = $callSiteVariances[$i] ?? TemplateTypeVariance::createInvariant();

            $selectedVariance = $callSiteVariance->invariant() && ($declarationSiteVariances[$i] ?? null) instanceof TemplateType
                ? $declarationSiteVariances[$i]->getVariance()
                : $callSiteVariance;

            $variance = $positionVariance !== null
                ? $positionVariance->compose(TypeLamParamVariance::fromClassTemplateTypeVariance($selectedVariance))
                : TypeLamParamVariance::fromClassTemplateTypeVariance($selectedVariance);

            yield from self::collect($type, $variance);
        }
    }

    /**
     * @return \Generator<int, TypeLamParamRef|TypeLamParamRefUnsupported>
     */
    private static function collectFromConstantArray(
        ConstantArrayType $constantArrayType,
        ?TypeLamParamVariance $positionVariance,
    ): \Generator {
        $variance = $positionVariance !== null
            ? $positionVariance->compose(TypeLamParamVariance::Covariant)
            : TypeLamParamVariance::Covariant;

        foreach ($constantArrayType->getKeyTypes() as $type) {
            yield from self::collect($type, $variance);
        }

        foreach ($constantArrayType->getValueTypes() as $type) {
            yield from self::collect($type, $variance);
        }
    }

    /**
     * @return \Generator<int, TypeLamParamRef|TypeLamParamRefUnsupported>
     */
    private static function collectFromObjectShape(
        ObjectShapeType $objectShapeType,
        ?TypeLamParamVariance $positionVariance,
    ): \Generator {
        $variance = $positionVariance !== null
            ? $positionVariance->compose(TypeLamParamVariance::Covariant)
            : TypeLamParamVariance::Covariant;

        foreach ($objectShapeType->getProperties() as $type) {
            yield from self::collect($type, $variance);
        }
    }

    /**
     * @return \Generator<int, TypeLamParamRef|TypeLamParamRefUnsupported>
     */
    private static function collectFromIterable(
        IterableType|ArrayType $iterableType,
        ?TypeLamParamVariance $positionVariance,
    ): \Generator {
        $variance = $positionVariance !== null
            ? $positionVariance->compose(TypeLamParamVariance::Covariant)
            : TypeLamParamVariance::Covariant;

        yield from self::collect($iterableType->getIterableKeyType(), $variance);
        yield from self::collect($iterableType->getIterableValueType(), $variance);
    }

    /**
     * @return \Generator<TypeLamParamRef|TypeLamParamRefUnsupported>
     */
    private static function collectFromCallable(
        ClosureType|CallableType $callableType,
        ?TypeLamParamVariance $positionVariance,
    ): \Generator {
        $parameterVariance = $positionVariance !== null
            ? $positionVariance->compose(TypeLamParamVariance::Contravariant)
            : TypeLamParamVariance::Contravariant;

        $returnVariance = $positionVariance !== null
            ? $positionVariance->compose(TypeLamParamVariance::Covariant)
            : TypeLamParamVariance::Covariant;

        foreach ($callableType->getParameters() as $parameter) {
            yield from self::collect($parameter->getType(), $parameterVariance);
        }

        yield from self::collect($callableType->getReturnType(), $returnVariance);
    }
}
