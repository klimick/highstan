<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\TypeClass;

use Highstan\HKEncoding\TypeLambda;

/**
 * @template F of TypeLambda
 * @extends Functor<F>
 */
interface Apply extends Functor
{
    /**
     * @template A
     * @template B
     *
     * @param F<A> $fa
     * @param F<callable(A): B> $fab
     * @return F<B>
     */
    public function apply(mixed $fa, mixed $fab): mixed;
}
