<?php

declare(strict_types=1);

namespace Highstan\Higher\Rule;

use Highstan\Higher\TypeLamApp;
use Highstan\Higher\WrappedTypeLam;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;

final readonly class InvalidTypeLamUsagesCollector
{
    /**
     * @return list<InvalidTypeLamUsage>
     */
    public static function collect(Type $fromType, Type $parentType, bool $isTypeLamAllowed = false): array
    {
        /** @var list<InvalidTypeLamUsage> */
        $invalidTypeLamUsages = [];

        TypeTraverser::map($fromType, static function (Type $t, callable $traverse) use (&$invalidTypeLamUsages, $parentType, $isTypeLamAllowed) {
            $collected = match (true) {
                $t instanceof WrappedTypeLam && !$isTypeLamAllowed => [new InvalidTypeLamUsage($t, $parentType)],
                $t instanceof GenericObjectType => self::collectFromGenericObject($t),
                $t instanceof TypeLamApp => self::collectFromTypeLamApp($t),
                default => null,
            };

            if ($collected !== null) {
                foreach ($collected as $invalidTypeLamUsage) {
                    $invalidTypeLamUsages[] = $invalidTypeLamUsage;
                }

                return $t;
            }

            return $traverse($t);
        });

        return $invalidTypeLamUsages;
    }

    /**
     * @return \Generator<int, InvalidTypeLamUsage>
     */
    private static function collectFromTypeLamApp(TypeLamApp $typeLamApp): \Generator
    {
        $typeLam = $typeLamApp->getTypeLamBound();

        foreach ($typeLamApp->getParams() as $i => $typeArgument) {
            yield from \array_key_exists($i, $typeLam->params)
                ? self::collect(
                    fromType: $typeArgument,
                    parentType: $typeLamApp,
                    isTypeLamAllowed: $typeLam->params[$i]->upperBound instanceof WrappedTypeLam,
                )
                : [];
        }
    }

    /**
     * @return \Generator<int, InvalidTypeLamUsage>
     */
    private static function collectFromGenericObject(GenericObjectType $genericObjectType): \Generator
    {
        $classReflection = $genericObjectType->getClassReflection();

        if ($classReflection === null) {
            return;
        }

        $upperBounds = $classReflection->typeMapToList($classReflection->getTemplateTypeMap()->resolveToBounds());

        foreach ($genericObjectType->getTypes() as $i => $typeArgument) {
            yield from \array_key_exists($i, $upperBounds)
                ? self::collect(
                    fromType: $typeArgument,
                    parentType: $genericObjectType,
                    isTypeLamAllowed: $upperBounds[$i] instanceof WrappedTypeLam,
                )
                : [];
        }
    }
}
