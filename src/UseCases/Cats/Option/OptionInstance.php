<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Option;

use Highstan\UseCases\Cats\TypeClass\Applicative;
use Highstan\UseCases\Cats\TypeClass\Monad;
use Highstan\UseCases\Cats\TypeClass\Traverse;

/**
 * @implements Monad<type-lam(x): Option<x>>
 * @implements Traverse<type-lam(x): Option<x>>
 */
final readonly class OptionInstance implements Monad, Traverse
{
    /**
     * @template A
     *
     * @param A $a
     * @return Option<A>
     */
    public function pure(mixed $a): Option
    {
        return Option::some($a);
    }

    /**
     * @template A
     * @template B
     *
     * @param Option<A> $fa
     * @param Option<callable(A): B> $fab
     * @return Option<B>
     */
    public function apply(mixed $fa, mixed $fab): Option
    {
        return $fa->apply($fab);
    }

    /**
     * @template A
     * @template B
     *
     * @param Option<A> $fa
     * @param callable(A): B $ab
     * @return Option<B>
     */
    public function map(mixed $fa, callable $ab): Option
    {
        return $fa->map($ab);
    }

    /**
     * @template A
     * @template B
     *
     * @param Option<A> $fa
     * @param callable(A): Option<B> $ab
     * @return Option<B>
     */
    public function flatMap(mixed $fa, callable $ab): Option
    {
        return $fa->flatMap($ab);
    }

    /**
     * @template A
     * @template B
     *
     * @param Option<A> $fa
     * @param B $zero
     * @param callable(B, A): B $reducer
     * @return B
     */
    public function fold(mixed $fa, mixed $zero, callable $reducer): mixed
    {
        return $fa->match(
            onNone: static fn() => $zero,
            onSome: static fn($a) => $reducer($zero, $a),
        );
    }

    /**
     * @template G of type-lam<_>
     * @template A
     * @template B
     *
     * @param Applicative<G> $G
     * @param Option<A> $fa
     * @param callable(A): G<B> $ab
     * @return G<Option<B>>
     */
    public function traverse(Applicative $G, mixed $fa, callable $ab): mixed
    {
        return $fa->traverse($G, $ab);
    }
}
