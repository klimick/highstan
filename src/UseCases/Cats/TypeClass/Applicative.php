<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\TypeClass;

/**
 * @template F of type-lam<_>
 * @extends Apply<F>
 */
interface Applicative extends Apply
{
    /**
     * @template A
     *
     * @param A $a
     * @return F<A>
     */
    public function pure(mixed $a): mixed;
}
