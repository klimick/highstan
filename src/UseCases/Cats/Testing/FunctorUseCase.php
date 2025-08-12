<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Testing;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;
use Highstan\UseCases\Cats\Either\EitherInstance;
use Highstan\UseCases\Cats\Either\EitherTypeLambda;
use Highstan\UseCases\Cats\Lst\LstInstance;
use Highstan\UseCases\Cats\Lst\LstTypeLambda;
use Highstan\UseCases\Cats\Option\OptionInstance;
use Highstan\UseCases\Cats\Option\OptionTypeLambda;
use Highstan\UseCases\Cats\TypeClass\Applicative;
use Highstan\UseCases\Cats\TypeClass\Functor;

final readonly class FunctorUseCase
{
    /**
     * @template F of TypeLambda
     *
     * @param Functor<F> $F
     * @param HK<F, int> $number
     * @return HK<F, string>
     */
    public function asString(Functor $F, HK $number): HK
    {
        $toString = static fn(int $str): string => "{$str}";

        return $F->map($number, $toString);
    }

    /**
     * @template F of TypeLambda
     *
     * @param Applicative<F> $F
     * @return HK<F, int>
     */
    public static function num(Applicative $F): HK
    {
        return $F->pure(42);
    }

    /**
     * @return HK<LstTypeLambda, string>
     */
    public function lst(LstInstance $lstI): HK
    {
        return $this->asString($lstI, self::num($lstI));
    }

    /**
     * @return HK<OptionTypeLambda, string>
     */
    public function option(OptionInstance $optionI): HK
    {
        return $this->asString($optionI, self::num($optionI));
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @return HK<EitherTypeLambda<Err>, string>
     */
    public function either(EitherInstance $eitherI): HK
    {
        return $this->asString($eitherI, self::num($eitherI));
    }
}
