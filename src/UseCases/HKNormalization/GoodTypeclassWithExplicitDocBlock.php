<?php

declare(strict_types=1);

namespace Highstan\UseCases\HKNormalization;

use Highstan\UseCases\Cats\Option\Option;

/**
 * @implements Typeclass<type-lam(x): Option<x>>
 */
final readonly class GoodTypeclassWithExplicitDocBlock implements Typeclass
{
    /**
     * @template A
     * @template B
     *
     * @param Option<A> $fa
     * @param callable(A): B $ab
     * @return Option<B>
     */
    public function m1(mixed $fa, callable $ab): mixed
    {
        return $fa->map($ab);
    }

    /**
     * @template A
     * @template B
     *
     * @param Option<A> $fa
     * @param callable(A): B $ab
     * @return Option<B>
     */
    public function m2(mixed $fa, callable $ab): mixed
    {
        return $fa->map($ab);
    }
}
