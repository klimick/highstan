<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\TypeClass;

/**
 * @template F of type-lam<_>
 */
interface Foldable
{
    /**
     * @template A
     * @template B
     *
     * @param F<A> $fa
     * @param B $zero
     * @param callable(B, A): B $reducer
     * @return B
     */
    public function fold(mixed $fa, mixed $zero, callable $reducer): mixed;
}
