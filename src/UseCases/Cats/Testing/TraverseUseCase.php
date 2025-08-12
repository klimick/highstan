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
     * @param callable(string): HK<G, int> $strToInt
     * @return HK<G, HK<F, int>>
     */
    public function traverse(Traverse $F, Applicative $G, HK $fa, callable $strToInt): HK
    {
        return $F->traverse($G, $fa, $strToInt);
    }

    /**
     * @param Lst<string> $lst
     * @param callable(string): Option<int> $strToInt
     * @return HK<OptionTypeLambda, HK<LstTypeLambda, int>>
     */
    public function lstOption(LstInstance $lstI, OptionInstance $optionI, Lst $lst, callable $strToInt): HK
    {
        return $this->traverse($lstI, $optionI, $lst, $strToInt);
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @param Option<string> $option
     * @param callable(string): Either<Err, int> $strToInt
     * @return HK<EitherTypeLambda<Err>, HK<OptionTypeLambda, int>>
     */
    public function optionEither(OptionInstance $optionI, EitherInstance $eitherI, Option $option, callable $strToInt): HK
    {
        return $this->traverse($optionI, $eitherI, $option, $strToInt);
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @param Either<Err, string> $either
     * @param callable(string): Option<int> $strToInt
     * @return HK<OptionTypeLambda, HK<EitherTypeLambda<Err>, int>>
     */
    public function eitherOption(EitherInstance $eitherI, OptionInstance $optionI, Either $either, callable $strToInt): HK
    {
        return $this->traverse($eitherI, $optionI, $either, $strToInt);
    }
}
