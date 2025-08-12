<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Lst;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;
use Highstan\UseCases\Cats\TypeClass\Applicative;
use Highstan\UseCases\Cats\TypeClass\Monad;
use Highstan\UseCases\Cats\TypeClass\Traverse;

/**
 * @implements Monad<LstTypeLambda>
 * @implements Traverse<LstTypeLambda>
 */
final readonly class LstInstance implements Monad, Traverse
{
    /**
     * @template A
     *
     * @param A $a
     * @return Lst<A>
     */
    public function pure(mixed $a): Lst
    {
        return Lst::of($a);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<LstTypeLambda, A> $fa
     * @param HK<LstTypeLambda, callable(A): B> $fab
     * @return Lst<B>
     */
    public function apply(mixed $fa, mixed $fab): Lst
    {
        return $fa->apply($fab);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<LstTypeLambda, A> $fa
     * @param B $zero
     * @param callable(B, A): B $reducer
     * @return B
     */
    public function fold(mixed $fa, mixed $zero, callable $reducer): mixed
    {
        return $fa->fold($zero, $reducer);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<LstTypeLambda, A> $fa
     * @param callable(A): HK<LstTypeLambda, B> $ab
     * @return Lst<B>
     */
    public function flatMap(mixed $fa, callable $ab): Lst
    {
        return $fa->flatMap($ab);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<LstTypeLambda, A> $fa
     * @param callable(A): B $ab
     * @return Lst<B>
     */
    public function map(mixed $fa, callable $ab): Lst
    {
        return $fa->map($ab);
    }

    /**
     * @template G of TypeLambda
     * @template A
     * @template B
     *
     * @param Applicative<G> $G
     * @param HK<LstTypeLambda, A> $fa
     * @param callable(A): G<B> $ab
     * @return G<HK<LstTypeLambda, B>>
     */
    public function traverse(Applicative $G, mixed $fa, callable $ab): mixed
    {
        return $fa->traverse($G, $ab);
    }
}
