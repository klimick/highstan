<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\TypeClass;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

/**
 * @template F of TypeLambda
 * @extends Applicative<F>
 */
interface Monad extends Applicative
{
    /**
     * @template A
     * @template B
     *
     * @param HK<F, A> $fa
     * @param callable(A): HK<F, B> $ab
     * @return HK<F, B>
     */
    public function flatMap(mixed $fa, callable $ab): mixed;
}
