<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Either;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

/**
 * @template-covariant PartiallyAppliedE
 */
final readonly class EitherTypeLambda implements TypeLambda
{
    /**
     * @template E
     * @template A
     *
     * @param HK<self<E>, A> $kind
     * @return Either<E, A>
     */
    public static function fix(HK $kind): Either
    {
        /** @var Either<E, A> */
        return $kind;
    }
}
