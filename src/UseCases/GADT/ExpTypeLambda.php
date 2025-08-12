<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

final readonly class ExpTypeLambda implements TypeLambda
{
    /**
     * @template A
     *
     * @param HK<self, A> $kind
     * @return Exp<A>
     */
    public static function fix(HK $kind): Exp
    {
        /** @var Exp<A> */
        return $kind;
    }
}
