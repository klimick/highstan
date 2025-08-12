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
     * @return Either<E, A>
     */
    public function pure(mixed $a): Either
    {
        return Either::right($a);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<EitherTypeLambda<E>, A> $fa
     * @param HK<EitherTypeLambda<E>, callable(A): B> $fab
     * @return Either<E, B>
     */
    public function apply(mixed $fa, mixed $fab): Either
    {
        return $fa->apply($fab);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<EitherTypeLambda<E>, A> $fa
     * @param callable(A): B $ab
     * @return Either<E, B>
     */
    public function map(mixed $fa, callable $ab): Either
    {
        return $fa->map($ab);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<EitherTypeLambda<E>, A> $fa
     * @param callable(A): HK<EitherTypeLambda<E>, B> $ab
     * @return Either<E, B>
     */
    public function flatMap(mixed $fa, callable $ab): Either
    {
        return $fa->flatMap($ab);
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
    public function fold(mixed $fa, mixed $zero, callable $reducer): mixed
    {
        return $fa->match(
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
     * @param callable(A): G<B> $ab
     * @return G<Either<E, B>>
     */
    public function traverse(Applicative $G, mixed $fa, callable $ab): mixed
    {
        return $fa->traverse($G, $ab);
    }
}
