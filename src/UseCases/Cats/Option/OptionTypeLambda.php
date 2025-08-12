<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Option;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

final readonly class OptionTypeLambda implements TypeLambda
{
    /**
     * @template A
     * @param HK<self, A> $kind
     * @return Option<A>
     */
    public static function fix(HK $kind): Option
    {
        /** @var Option<A> */
        return $kind;
    }
}
