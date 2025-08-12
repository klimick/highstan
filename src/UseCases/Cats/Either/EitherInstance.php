<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Either;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;
use Highstan\UseCases\Cats\TypeClass\Applicative;
use Highstan\UseCases\Cats\TypeClass\Monad;
use Highstan\UseCases\Cats\TypeClass\Traverse;

/**
 * @template E
 * @implements Monad<EitherTypeLambda<E>>
 * @implements Traverse<EitherTypeLambda<E>>
 */
final readonly class EitherInstance implements Monad, Traverse
{
    /**
     * @template A
     *
     * @param A $a
     * @return HK<EitherTypeLambda<E>, A>
     */
    public function pure(mixed $a): HK
    {
        return Either::right($a);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<EitherTypeLambda<E>, A> $fa
     * @param HK<EitherTypeLambda<E>, callable(A): B> $fab
     * @return HK<EitherTypeLambda<E>, B>
     */
    public function apply(HK $fa, HK $fab): HK
    {
        return EitherTypeLambda::fix($fa)->apply(EitherTypeLambda::fix($fab));
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<EitherTypeLambda<E>, A> $fa
     * @param callable(A): B $ab
     * @return HK<EitherTypeLambda<E>, B>
     */
    public function map(HK $fa, callable $ab): HK
    {
        return EitherTypeLambda::fix($fa)->map($ab);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<EitherTypeLambda<E>, A> $fa
     * @param callable(A): HK<EitherTypeLambda<E>, B> $ab
     * @return HK<EitherTypeLambda<E>, B>
     */
    public function flatMap(HK $fa, callable $ab): HK
    {
        return EitherTypeLambda::fix($fa)->flatMap(
            static fn($a) => EitherTypeLambda::fix($ab($a)),
        );
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<EitherTypeLambda<E>, A> $fa
     * @param B $zero
     * @param callable(B, A): B $reducer
     * @return B
     */
    public function fold(HK $fa, mixed $zero, callable $reducer): mixed
    {
        return EitherTypeLambda::fix($fa)->match(
            onLeft: static fn() => $zero,
            onRight: static fn($a) => $reducer($zero, $a),
        );
    }

    /**
     * @template G of TypeLambda
     * @template A
     * @template B
     *
     * @param Applicative<G> $G
     * @param HK<EitherTypeLambda<E>, A> $fa
     * @param callable(A): HK<G, B> $ab
     * @return HK<G, HK<EitherTypeLambda<E>, B>>
     */
    public function traverse(Applicative $G, HK $fa, callable $ab): HK
    {
        return EitherTypeLambda::fix($fa)->traverse($G, $ab);
    }
}
