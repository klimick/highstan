<?php

declare(strict_types=1);

namespace Highstan\Higher\Rule;

use Highstan\Higher\TypeLamApp;
use Highstan\Higher\WrappedTypeLam;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;

final readonly class TypeCollector
{
    /**
     * @template T of Type
     *
     * @param class-string<T> $classOfType
     * @param Type|list<Type> $from
     * @return list<T>
     */
    public static function collect(string $classOfType, Type|array $from): array
    {
        /** @var list<T> */
        $collected = [];

        /** @var bool */
        $typeLamMeet = false;

        foreach ($from instanceof Type ? [$from] : $from as $current) {
            TypeTraverser::map($current, static function (Type $t, callable $traverse) use ($classOfType, &$collected, &$typeLamMeet) {
                if ($t instanceof $classOfType) {
                    $collected[] = $t;
                }

                if ($t instanceof TypeLamApp) {
                    $prevTypeLamMeet = $typeLamMeet;
                    $typeLamMeet = true;

                    try {
                        return $traverse($t);
                    } finally {
                        $typeLamMeet = $prevTypeLamMeet;
                    }
                }

                // Avoid duplicating rule errors.
                if ($typeLamMeet && $t instanceof TemplateType && $t->getBound() instanceof WrappedTypeLam) {
                    return $t;
                }

                return $traverse($t);
            });
        }

        return $collected;
    }
}
