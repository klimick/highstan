<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Lst;

use Highstan\UseCases\Cats\TypeClass\Applicative;
use Highstan\UseCases\Cats\TypeClass\Monad;
use Highstan\UseCases\Cats\TypeClass\Traverse;

/**
 * @implements Monad<type-lam(x): Lst<x>>
 * @implements Traverse<type-lam(x): Lst<x>>
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
     * @param Lst<A> $fa
     * @param Lst<callable(A): B> $fab
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
     * @param Lst<A> $fa
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
     * @param Lst<A> $fa
     * @param callable(A): Lst<B> $ab
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
     * @param Lst<A> $fa
     * @param callable(A): B $ab
     * @return Lst<B>
     */
    public function map(mixed $fa, callable $ab): Lst
    {
        return $fa->map($ab);
    }

    /**
     * @template G of type-lam<_>
     * @template A
     * @template B
     *
     * @param Applicative<G> $G
     * @param Lst<A> $fa
     * @param callable(A): G<B> $ab
     * @return G<Lst<B>>
     */
    public function traverse(Applicative $G, mixed $fa, callable $ab): mixed
    {
        return $fa->traverse($G, $ab);
    }
}
