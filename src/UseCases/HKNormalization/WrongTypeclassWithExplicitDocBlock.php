<?php

declare(strict_types=1);

namespace Highstan\UseCases\HKNormalization;

use Highstan\UseCases\Cats\Option\Option;

/**
 * @implements Typeclass<type-lam(x): Option<x>>
 */
final readonly class WrongTypeclassWithExplicitDocBlock implements Typeclass
{
    /**
     * @template A
     * @template B
     *
     * @param int $fa
     * @param callable(A): B $ab
     * @return Option<B>
     *
     * Ok. (int $fa) =!= (Option<A> $fa)
     * @phpstan-ignore method.childParameterType
     */
    public function m1(mixed $fa, callable $ab): mixed
    {
        throw new \RuntimeException('???');
    }

    /**
     * @template A
     * @template B
     *
     * @param Option<A> $fa
     * @param callable(A): B $ab
     * @return int
     *
     * Ok. int =!= Option<B>
     * @phpstan-ignore method.childReturnType
     */
    public function m2(mixed $fa, callable $ab): mixed
    {
        throw new \RuntimeException('???');
    }
}
