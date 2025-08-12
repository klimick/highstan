<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\TypeClass;

use Highstan\HKEncoding\HK;
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
     * @param HK<F, A> $fa
     * @param HK<F, callable(A): B> $fab
     * @return HK<F, B>
     */
    public function apply(mixed $fa, mixed $fab): mixed;
}
