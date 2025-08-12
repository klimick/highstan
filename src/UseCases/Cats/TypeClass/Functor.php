<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\TypeClass;

use Highstan\HKEncoding\TypeLambda;

/**
 * @template F of TypeLambda
 */
interface Functor
{
    /**
     * @template A
     * @template B
     *
     * @param F<A> $fa
     * @param callable(A): B $ab
     * @return F<B>
     */
    public function map(mixed $fa, callable $ab): mixed;
}
