<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Option;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;
use Highstan\UseCases\Cats\TypeClass\Applicative;
use Highstan\UseCases\Cats\TypeClass\Monad;
use Highstan\UseCases\Cats\TypeClass\Traverse;

/**
 * @implements Monad<OptionTypeLambda>
 * @implements Traverse<OptionTypeLambda>
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
     * @param HK<OptionTypeLambda, A> $fa
     * @param HK<OptionTypeLambda, callable(A): B> $fab
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
     * @param HK<OptionTypeLambda, A> $fa
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
     * @param HK<OptionTypeLambda, A> $fa
     * @param callable(A): HK<OptionTypeLambda, B> $ab
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
     * @param HK<OptionTypeLambda, A> $fa
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
     * @template G of TypeLambda
     * @template A
     * @template B
     *
     * @param Applicative<G> $G
     * @param HK<OptionTypeLambda, A> $fa
     * @param callable(A): HK<G, B> $ab
     * @return HK<G, Option<B>>
     */
    public function traverse(Applicative $G, mixed $fa, callable $ab): mixed
    {
        return $fa->traverse($G, $ab);
    }
}
