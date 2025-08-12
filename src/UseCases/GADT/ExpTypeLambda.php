<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

interface ExpTypeLambda extends TypeLambda
{
    /**
     * @template A
     *
     * @param HK<self, A> $kind
     * @return Exp<A>
     */
    public static function fix(mixed $kind): Exp;
}
