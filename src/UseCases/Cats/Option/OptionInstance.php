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
     * @return HK<OptionTypeLambda, A>
     */
    public function pure(mixed $a): HK
    {
        return Option::some($a);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<OptionTypeLambda, A> $fa
     * @param HK<OptionTypeLambda, callable(A): B> $fab
     * @return HK<OptionTypeLambda, B>
     */
    public function apply(HK $fa, HK $fab): HK
    {
        return OptionTypeLambda::fix($fa)->apply(OptionTypeLambda::fix($fab));
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<OptionTypeLambda, A> $fa
     * @param callable(A): B $ab
     * @return HK<OptionTypeLambda, B>
     */
    public function map(HK $fa, callable $ab): HK
    {
        return OptionTypeLambda::fix($fa)->map($ab);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<OptionTypeLambda, A> $fa
     * @param callable(A): HK<OptionTypeLambda, B> $ab
     * @return HK<OptionTypeLambda, B>
     */
    public function flatMap(HK $fa, callable $ab): HK
    {
        return OptionTypeLambda::fix($fa)->flatMap(
            static fn($a) => OptionTypeLambda::fix($ab($a)),
        );
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
    public function fold(HK $fa, mixed $zero, callable $reducer): mixed
    {
        return OptionTypeLambda::fix($fa)->match(
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
     * @return HK<G, HK<OptionTypeLambda, B>>
     */
    public function traverse(Applicative $G, HK $fa, callable $ab): HK
    {
        return OptionTypeLambda::fix($fa)->traverse($G, $ab);
    }
}
