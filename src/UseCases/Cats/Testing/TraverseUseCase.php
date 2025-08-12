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
use Highstan\UseCases\Cats\TypeClass\Traverse;

final readonly class TraverseUseCase
{
    /**
     * @template F of TypeLambda
     * @template G of TypeLambda
     *
     * @param Traverse<F> $F
     * @param Applicative<G> $G
     * @param HK<F, string> $fa
     * @param callable(string): HK<G, int> $ab
     * @return HK<G, HK<F, int>>
     */
    public function traverse(Traverse $F, Applicative $G, mixed $fa, callable $ab): mixed
    {
        return $F->traverse($G, $fa, $ab);
    }

    /**
     * @param Option<string> $option
     * @param callable(string): Option<int> $f
     * @return Option<Option<int>>
     */
    public function optionOption(OptionInstance $optionI, Option $option, callable $f): Option
    {
        return $this->traverse($optionI, $optionI, $option, $f);
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @param Option<string> $option
     * @param callable(string): Either<Err, int> $f
     * @return Either<Err, Option<int>>
     */
    public function optionEither(OptionInstance $optionI, EitherInstance $eitherI, Option $option, callable $f): Either
    {
        return $this->traverse($optionI, $eitherI, $option, $f);
    }

    /**
     * @param Option<string> $option
     * @param callable(string): Lst<int> $f
     * @return Lst<Option<int>>
     */
    public function optionLst(OptionInstance $optionI, LstInstance $lstI, Option $option, callable $f): Lst
    {
        return $this->traverse($optionI, $lstI, $option, $f);
    }

    /**
     * @param EitherInstance<Err> $eitherI1
     * @param EitherInstance<OtherErr> $eitherI2
     * @param Either<Err, string> $either
     * @param callable(string): Either<OtherErr, int> $f
     * @return Either<OtherErr, Either<Err, int>>
     */
    public function eitherEither(EitherInstance $eitherI1, EitherInstance $eitherI2, Either $either, callable $f): Either
    {
        return $this->traverse($eitherI1, $eitherI2, $either, $f);
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @param Either<Err, string> $either
     * @param callable(string): Option<int> $f
     * @return Option<Either<Err, int>>
     */
    public function eitherOption(EitherInstance $eitherI, OptionInstance $optionI, Either $either, callable $f): Option
    {
        return $this->traverse($eitherI, $optionI, $either, $f);
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @param Either<Err, string> $either
     * @param callable(string): Lst<int> $f
     * @return Lst<Either<Err, int>>
     */
    public function eitherLst(EitherInstance $eitherI, LstInstance $lstI, Either $either, callable $f): Lst
    {
        return $this->traverse($eitherI, $lstI, $either, $f);
    }

    /**
     * @param Lst<string> $lst
     * @param callable(string): Lst<int> $f
     * @return Lst<Lst<int>>
     */
    public function lstLst(LstInstance $lstI, Lst $lst, callable $f): Lst
    {
        return $this->traverse($lstI, $lstI, $lst, $f);
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @param Lst<string> $lst
     * @param callable(string): Either<Err, int> $f
     * @return Either<Err, Lst<int>>
     */
    public function lstEither(LstInstance $lstI, EitherInstance $eitherI, Lst $lst, callable $f): Either
    {
        return $this->traverse($lstI, $eitherI, $lst, $f);
    }

    /**
     * @param Lst<string> $lst
     * @param callable(string): Option<int> $f
     * @return Option<Lst<int>>
     */
    public function lstOption(LstInstance $lstI, OptionInstance $optionI, Lst $lst, callable $f): Option
    {
        return $this->traverse($lstI, $optionI, $lst, $f);
    }
}
