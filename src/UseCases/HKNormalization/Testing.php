<?php

declare(strict_types=1);

namespace Highstan\UseCases\HKNormalization;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;
use Highstan\UseCases\Cats\Lst\Lst;
use Highstan\UseCases\Cats\Lst\LstTypeLambda;

final readonly class Testing
{
    /**
     * @param HK<LstTypeLambda, int> $kind
     * @return Lst<int>
     */
    public function concreteLambda(mixed $kind): Lst
    {
        // Ok.
        // Because HK<LstTypeLambda, int> normalized to List<int>.
        return $kind;
    }

    /**
     * @template F of TypeLambda
     *
     * @param HK<F, int> $kind
     * @return Lst<int>
     */
    public function abstractLambda(mixed $kind): Lst
    {
        // Intentionally ignored. For testing purposes.
        // @phpstan-ignore return.type
        return $kind;
    }

    /**
     * @template F of TypeLambda
     *
     * @param Typeclass<F> $tc1
     * @param Typeclass<LstTypeLambda> $tc2
     * @return HK<F, Lst<int>>
     */
    public function abstractWithConcreteTypeLambda(Typeclass $tc1, Typeclass $tc2): mixed
    {
        return $this($tc2, $tc1);
    }

    /**
     * @template F of TypeLambda
     *
     * @param Typeclass<F> $tc1
     * @param Typeclass<LstTypeLambda> $tc2
     * @return Lst<HK<F, int>>
     */
    public function concreteWithAbstractLambda(Typeclass $tc1, Typeclass $tc2): Lst
    {
        return $this($tc1, $tc2);
    }

    /**
     * @template F of TypeLambda
     * @template G of TypeLambda
     *
     * @param Typeclass<F> $F
     * @param Typeclass<G> $G
     * @return HK<G, HK<F, int>>
     */
    public function __invoke(Typeclass $F, Typeclass $G): HK
    {
        throw new \RuntimeException('???');
    }
}
