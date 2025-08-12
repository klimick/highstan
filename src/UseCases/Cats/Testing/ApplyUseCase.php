<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Testing;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;
use Highstan\UseCases\Cats\Either\Either;
use Highstan\UseCases\Cats\Either\EitherInstance;
use Highstan\UseCases\Cats\Either\EitherTypeLambda;
use Highstan\UseCases\Cats\Lst\Lst;
use Highstan\UseCases\Cats\Lst\LstInstance;
use Highstan\UseCases\Cats\Lst\LstTypeLambda;
use Highstan\UseCases\Cats\Option\Option;
use Highstan\UseCases\Cats\Option\OptionInstance;
use Highstan\UseCases\Cats\Option\OptionTypeLambda;
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
    public function toString(Apply $F, HK $number, HK $toString): HK
    {
        return $F->apply($number, $toString);
    }

    /**
     * @template F of TypeLambda
     *
     * @param Applicative<F> $F
     * @return HK<F, callable(int): string>
     */
    public static function stringifier(Applicative $F): HK
    {
        return $F->pure(static fn(int $n): string => "{$n}");
    }

    /**
     * @param Lst<int> $numbers
     * @return HK<LstTypeLambda, string>
     */
    public function lst(LstInstance $lstI, Lst $numbers): HK
    {
        return $this->toString($lstI, $numbers, self::stringifier($lstI));
    }

    /**
     * @param Option<int> $number
     * @return HK<OptionTypeLambda, string>
     */
    public function option(OptionInstance $optionI, Option $number): HK
    {
        return $this->toString($optionI, $number, self::stringifier($optionI));
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @param Either<Err, int> $number
     * @return HK<EitherTypeLambda<Err>, string>
     */
    public function either(EitherInstance $eitherI, Either $number): HK
    {
        return $this->toString($eitherI, $number, self::stringifier($eitherI));
    }
}
