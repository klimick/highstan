<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\TypeClass;

/**
 * @template F of type-lam<_>
 * @extends Applicative<F>
 */
interface Monad extends Applicative
{
    /**
     * @template A
     * @template B
     *
     * @param F<A> $fa
     * @param callable(A): F<B> $ab
     * @return F<B>
     */
    public function flatMap(mixed $fa, callable $ab): mixed;
}
