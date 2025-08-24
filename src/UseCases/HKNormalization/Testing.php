<?php

declare(strict_types=1);

namespace Highstan\UseCases\HKNormalization;

use Highstan\UseCases\Cats\Lst\Lst;

final readonly class Testing
{
    /**
     * @template F of type-lam<_>
     *
     * @param F<int> $kind
     * @return Lst<int>
     */
    public function abstractLambda(mixed $kind): Lst
    {
        // Error.
        // F<int> is unknown type constructor and cannot be normalized to Lst<int>.
        // @phpstan-ignore return.type
        return $kind;
    }

    /**
     * @template F of type-lam<_>
     *
     * @param Typeclass<F> $tc1
     * @param Typeclass<type-lam(x): Lst<x>> $tc2
     * @return F<Lst<int>>
     */
    public function abstractWithConcreteTypeLambda(Typeclass $tc1, Typeclass $tc2): mixed
    {
        return $this($tc2, $tc1);
    }

    /**
     * @template F of type-lam<_>
     *
     * @param Typeclass<F> $tc1
     * @param Typeclass<type-lam(x): Lst<x>> $tc2
     * @return Lst<F<int>>
     */
    public function concreteWithAbstractLambda(Typeclass $tc1, Typeclass $tc2): Lst
    {
        return $this($tc1, $tc2);
    }

    /**
     * @template F of type-lam<_>
     *
     * @param Typeclass<F> $tc1
     * @param Typeclass<type-lam(x): Lst<x>> $tc2
     * @return Lst<F<string>>
     */
    public function concreteWithAbstractLambdaErr(Typeclass $tc1, Typeclass $tc2): Lst
    {
        // Ok. Lst<F<string>> =!= Lst<F<int>>
        // @phpstan-ignore return.type
        return $this($tc1, $tc2);
    }

    /**
     * @template F of type-lam<_>
     * @template G of type-lam<_>
     *
     * @param Typeclass<F> $tc1
     * @param Typeclass<G> $tc2
     * @return F<G<int>>
     */
    public function onlyAbstract(Typeclass $tc1, Typeclass $tc2): mixed
    {
        return $this($tc2, $tc1);
    }

    /**
     * @template F of type-lam<_>
     * @template G of type-lam<_>
     *
     * @param Typeclass<F> $tc1
     * @param Typeclass<G> $tc2
     * @return F<G<int>>
     */
    public function onlyAbstractError(Typeclass $tc1, Typeclass $tc2): mixed
    {
        // Ok. F<G<int>> =!= G<F<int>>
        // @phpstan-ignore return.type
        return $this($tc1, $tc2);
    }

    /**
     * @template F of type-lam<_>
     * @template G of type-lam<_>
     *
     * @param Typeclass<F> $F
     * @param Typeclass<G> $G
     * @return G<F<int>>
     */
    public function __invoke(Typeclass $F, Typeclass $G): mixed
    {
        throw new \RuntimeException('???');
    }
}
