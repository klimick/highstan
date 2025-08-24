<?php

declare(strict_types=1);

namespace Highstan\UseCases\HKNormalization;

/**
 * @template F of type-lam<_>
 */
interface Typeclass
{
    /**
     * @template A
     * @template B
     *
     * @param F<A> $fa
     * @param callable(A): B $ab
     * @return F<B>
     */
    public function m1(mixed $fa, callable $ab): mixed;

    /**
     * @template A
     * @template B
     *
     * @param F<A> $fa
     * @param callable(A): B $ab
     * @return F<B>
     */
    public function m2(mixed $fa, callable $ab): mixed;
}
