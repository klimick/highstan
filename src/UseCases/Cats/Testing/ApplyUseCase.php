<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Testing;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;
use Highstan\UseCases\Cats\Either\Either;
use Highstan\UseCases\Cats\Either\EitherInstance;
use Highstan\UseCases\Cats\Lst\Lst;
use Highstan\UseCases\Cats\Lst\LstInstance;
use Highstan\UseCases\Cats\Option\Option;
use Highstan\UseCases\Cats\Option\OptionInstance;
use Highstan\UseCases\Cats\TypeClass\Applicative;
use Highstan\UseCases\Cats\TypeClass\Apply;

final readonly class ApplyUseCase
{
    /**
     * @template F of TypeLambda
     *
     * @param Apply<F> $F
     * @param HK<F, int> $number
     * @param HK<F, callable(int): string> $toString
     * @return HK<F, string>
     */
    public function toString(Apply $F, mixed $number, mixed $toString): mixed
    {
        return $F->apply($number, $toString);
    }

    /**
     * @template F of TypeLambda
     *
     * @param Applicative<F> $F
     * @return HK<F, callable(int): string>
     */
    public static function stringifier(Applicative $F): mixed
    {
        return $F->pure(static fn(int $n): string => "{$n}");
    }

    /**
     * @param Lst<int> $numbers
     * @return Lst<string>
     */
    public function lst(LstInstance $lstI, Lst $numbers): Lst
    {
        return $this->toString($lstI, $numbers, self::stringifier($lstI));
    }

    /**
     * @param Option<int> $number
     * @return Option<string>
     */
    public function option(OptionInstance $optionI, Option $number): Option
    {
        return $this->toString($optionI, $number, self::stringifier($optionI));
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @param Either<Err, int> $number
     * @return Either<Err, string>
     */
    public function either(EitherInstance $eitherI, Either $number): Either
    {
        return $this->toString($eitherI, $number, self::stringifier($eitherI));
    }
}
