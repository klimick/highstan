<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

interface EvaluateTypeLambda extends TypeLambda
{
    /**
     * @template A
     *
     * @param HK<self, A> $kind
     * @return Evaluate<A>
     */
    public static function fix(mixed $kind): Evaluate;
}
