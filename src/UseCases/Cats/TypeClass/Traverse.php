<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\TypeClass;

/**
 * @template F of type-lam<_>
 * @extends Functor<F>
 * @extends Foldable<F>
 */
interface Traverse extends Functor, Foldable
{
    /**
     * @template G of type-lam<_>
     * @template A
     * @template B
     *
     * @param Applicative<G> $G
     * @param F<A> $fa
     * @param callable(A): G<B> $ab
     * @return G<F<B>>
     */
    public function traverse(Applicative $G, mixed $fa, callable $ab): mixed;
}
