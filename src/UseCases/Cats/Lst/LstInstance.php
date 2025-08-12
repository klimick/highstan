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
     * @return HK<LstTypeLambda, A>
     */
    public function pure(mixed $a): HK
    {
        return Lst::of($a);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<LstTypeLambda, A> $fa
     * @param HK<LstTypeLambda, callable(A): B> $fab
     * @return HK<LstTypeLambda, B>
     */
    public function apply(HK $fa, HK $fab): HK
    {
        return LstTypeLambda::fix($fa)->apply(LstTypeLambda::fix($fab));
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
    public function fold(HK $fa, mixed $zero, callable $reducer): mixed
    {
        return LstTypeLambda::fix($fa)->fold($zero, $reducer);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<LstTypeLambda, A> $fa
     * @param callable(A): B $ab
     * @return HK<LstTypeLambda, B>
     */
    public function map(HK $fa, callable $ab): HK
    {
        return LstTypeLambda::fix($fa)->map($ab);
    }

    /**
     * @template A
     * @template B
     *
     * @param HK<LstTypeLambda, A> $fa
     * @param callable(A): HK<LstTypeLambda, B> $ab
     * @return HK<LstTypeLambda, B>
     */
    public function flatMap(HK $fa, callable $ab): HK
    {
        return LstTypeLambda::fix($fa)->flatMap(
            static fn($a) => LstTypeLambda::fix($ab($a)),
        );
    }

    /**
     * @template G of TypeLambda
     * @template A
     * @template B
     *
     * @param Applicative<G> $G
     * @param HK<LstTypeLambda, A> $fa
     * @param callable(A): HK<G, B> $ab
     * @return HK<G, HK<LstTypeLambda, B>>
     */
    public function traverse(Applicative $G, HK $fa, callable $ab): HK
    {
        return LstTypeLambda::fix($fa)->traverse($G, $ab);
    }
}
