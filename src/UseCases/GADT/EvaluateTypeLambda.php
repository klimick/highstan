<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

final readonly class EvaluateTypeLambda implements TypeLambda
{
    /**
     * @template A
     *
     * @param HK<self, A> $kind
     * @return Evaluate<A>
     */
    public static function fix(HK $kind): Evaluate
    {
        /** @var Evaluate<A> */
        return $kind;
    }
}
