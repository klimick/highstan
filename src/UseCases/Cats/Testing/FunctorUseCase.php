<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Testing;

use Highstan\HKEncoding\TypeLambda;
use Highstan\UseCases\Cats\Either\Either;
use Highstan\UseCases\Cats\Either\EitherInstance;
use Highstan\UseCases\Cats\Lst\Lst;
use Highstan\UseCases\Cats\Lst\LstInstance;
use Highstan\UseCases\Cats\Option\Option;
use Highstan\UseCases\Cats\Option\OptionInstance;
use Highstan\UseCases\Cats\TypeClass\Applicative;
use Highstan\UseCases\Cats\TypeClass\Functor;

final readonly class FunctorUseCase
{
    /**
     * @template F of TypeLambda
     *
     * @param Functor<F> $F
     * @param F<int> $number
     * @return F<string>
     */
    public function asString(Functor $F, mixed $number): mixed
    {
        $toString = static fn(int $str): string => "{$str}";

        return $F->map($number, $toString);
    }

    /**
     * @template F of TypeLambda
     *
     * @param Applicative<F> $F
     * @return F<int>
     */
    public static function num(Applicative $F): mixed
    {
        return $F->pure(42);
    }

    /**
     * @return Lst<string>
     */
    public function lst(LstInstance $lstI): Lst
    {
        return $this->asString($lstI, self::num($lstI));
    }

    /**
     * @return Option<string>
     */
    public function option(OptionInstance $optionI): Option
    {
        return $this->asString($optionI, self::num($optionI));
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @return Either<Err, string>
     */
    public function either(EitherInstance $eitherI): Either
    {
        return $this->asString($eitherI, self::num($eitherI));
    }
}
