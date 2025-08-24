<?php

declare(strict_types=1);

namespace Highstan\UseCases\HKNormalization;

use Highstan\UseCases\Cats\Option\Option;

/**
 * @implements Typeclass<type-lam(x): Option<x>>
 */
final readonly class GoodTypeclassWithInheritedDocBlock implements Typeclass
{
    public function m1(mixed $fa, callable $ab): mixed
    {
        return $fa->map($ab);
    }

    public function m2(mixed $fa, callable $ab): mixed
    {
        return $fa->map($ab);
    }
}
