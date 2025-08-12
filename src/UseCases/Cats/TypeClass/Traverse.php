<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\TypeClass;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

/**
 * @template F of TypeLambda
 * @extends Functor<F>
 * @extends Foldable<F>
 */
interface Traverse extends Functor, Foldable
{
    /**
     * @template G of TypeLambda
     * @template A
     * @template B
     *
     * @param Applicative<G> $G
     * @param HK<F, A> $fa
     * @param callable(A): HK<G, B> $ab
     * @return HK<G, HK<F, B>>
     */
    public function traverse(Applicative $G, mixed $fa, callable $ab): mixed;
}
