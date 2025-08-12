<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Lst;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

final readonly class LstTypeLambda implements TypeLambda
{
    /**
     * @template A
     * @param HK<self, A> $kind
     * @return Lst<A>
     */
    public static function fix(HK $kind): Lst
    {
        /** @var Lst<A> */
        return $kind;
    }
}
