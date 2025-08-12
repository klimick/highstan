<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Lst;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;
use Highstan\UseCases\Cats\TypeClass\Applicative;

/**
 * @template-covariant A = never
 * @implements HK<LstTypeLambda, A>
 * @implements \IteratorAggregate<int, A>
 */
final readonly class Lst implements HK, \IteratorAggregate
{
    /**
     * @param list<A> $fa
     */
    private function __construct(
        private array $fa = [],
    ) {}

    /**
     * @template T
     *
     * @param T ...$elems
     * @return self<T>
     *
     * @no-named-arguments
     */
    public static function of(mixed ...$elems): self
    {
        return new self($elems);
    }

    /**
     * @return self<never>
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @template B
     * @param B $b
     * @return self<A|B>
     */
    public function with(mixed $b): self
    {
        return new self([...$this->fa, $b]);
    }

    /**
     * @template B
     *
     * @param callable(A): B $ab
     * @return self<B>
     */
    public function map(callable $ab): self
    {
        return new self(array_map($ab, $this->fa));
    }

    /**
     * @template B
     *
     * @param callable(A): Lst<B> $ab
     * @return self<B>
     */
    public function flatMap(callable $ab): self
    {
        $fb = [];

        foreach ($this as $a) {
            foreach ($ab($a) as $b) {
                $fb[] = $b;
            }
        }

        return new self($fb);
    }

    /**
     * @template B
     *
     * @param self<callable(A): B> $fab
     * @return self<B>
     */
    public function apply(self $fab): self
    {
        $fb = [];

        foreach ($this->fa as $a) {
            foreach ($fab as $ab) {
                $fb[] = $ab($a);
            }
        }

        return new self($fb);
    }

    /**
     * @template G of TypeLambda
     * @template B
     *
     * @param Applicative<G> $G
     * @param callable(A): HK<G, B> $ab
     * @return HK<G, Lst<B>>
     */
    public function traverse(Applicative $G, callable $ab): mixed
    {
        /**
         * @var HK<G, self<B>> $gfb
         * @phpstan-ignore varTag.type
         */
        $gfb = $G->pure(new self());

        /**
         * @var callable(Lst<B>): (callable(B): Lst<B>) $addToList
         * @phpstan-ignore varTag.nativeType
         */
        $addToList = static fn(Lst $fb) => $fb->with(...);

        foreach ($this as $a) {
            /**
             * @var HK<G, self<B>> $gfb
             * @phpstan-ignore varTag.type, argument.type
             */
            $gfb = $G->apply($ab($a), $G->map($gfb, $addToList));
        }

        return $gfb;
    }

    /**
     * @template B
     *
     * @param B $zero
     * @param callable(B, A): B $reducer
     * @return B
     */
    public function fold(mixed $zero, callable $reducer): mixed
    {
        $acc = $zero;

        foreach ($this as $a) {
            $acc = $reducer($acc, $a);
        }

        return $acc;
    }

    /**
     * @return list<A>
     */
    public function toArray(): array
    {
        return $this->fa;
    }

    /**
     * @return \Traversable<int, A>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->fa;
    }
}
