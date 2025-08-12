<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\TypeClass;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

/**
 * @template F of TypeLambda
 * @extends Apply<F>
 */
interface Applicative extends Apply
{
    /**
     * @template A
     *
     * @param A $a
     * @return HK<F, A>
     */
    public function pure(mixed $a): mixed;
}
