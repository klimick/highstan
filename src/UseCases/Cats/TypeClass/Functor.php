<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\TypeClass;

use Highstan\HKEncoding\HK;
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
     * @param HK<F, A> $fa
     * @param callable(A): B $ab
     * @return HK<F, B>
     */
    public function map(HK $fa, callable $ab): HK;
}
