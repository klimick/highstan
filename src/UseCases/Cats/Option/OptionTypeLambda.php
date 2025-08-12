<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Option;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

interface OptionTypeLambda extends TypeLambda
{
    /**
     * @template A
     * @param HK<self, A> $kind
     * @return Option<A>
     */
    public static function fix(HK $kind): Option;
}
