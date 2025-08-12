<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Either;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;
use Highstan\UseCases\Cats\TypeClass\Applicative;

/**
 * @template-covariant E = never
 * @template-covariant A = never
 * @implements HK<EitherTypeLambda<E>, A>
 */
final readonly class Either implements HK
{
    /**
     * @param ( array{type: 'left', left: E}
     *        | array{type: 'right', right: A} ) $data
     */
    private function __construct(
        private array $data,
    ) {}

    /**
     * @template T
     *
     * @param T $value
     * @return self<T, never>
     */
    public static function left(mixed $value): self
    {
        return new self(['type' => 'left', 'left' => $value]);
    }

    /**
     * @template T
     *
     * @param T $value
     * @return self<never, T>
     */
    public static function right(mixed $value): self
    {
        return new self(['type' => 'right', 'right' => $value]);
    }

    /**
     * @template B
     *
     * @param callable(A): B $ab
     * @return self<E, B>
     */
    public function map(callable $ab): self
    {
        if ($this->data['type'] === 'left') {
            return self::left($this->data['left']);
        }

        return self::right($ab($this->data['right']));
    }

    /**
     * @template B
     * @template E2
     *
     * @param callable(A): Either<E2, B> $ab
     * @return self<E | E2, B>
     */
    public function flatMap(callable $ab): self
    {
        if ($this->data['type'] === 'left') {
            return self::left($this->data['left']);
        }

        return $ab($this->data['right']);
    }

    /**
     * @template B
     * @template E2
     *
     * @param Either<E2, callable(A): B> $ab
     * @return self<E | E2, B>
     */
    public function apply(self $ab): self
    {
        if ($this->data['type'] === 'left') {
            return self::left($this->data['left']);
        }

        if ($ab->data['type'] === 'left') {
            return self::left($ab->data['left']);
        }

        return self::right($ab->data['right']($this->data['right']));
    }

    /**
     * @template G of TypeLambda
     * @template B
     *
     * @param Applicative<G> $G
     * @param callable(A): HK<G, B> $ab
     * @return HK<G, Either<E, B>>
     */
    public function traverse(Applicative $G, callable $ab): HK
    {
        return $this->match(
            onLeft: static fn($e) => $G->pure(Either::left($e)),
            /** @phpstan-ignore argument.type */
            onRight: static fn($a) => $G->map($ab($a), Either::right(...)),
        );
    }

    /**
     * @template EOut
     * @template AOut
     *
     * @param callable(E): EOut $onLeft
     * @param callable(A): AOut $onRight
     * @return EOut|AOut
     */
    public function match(callable $onLeft, callable $onRight): mixed
    {
        if ($this->data['type'] === 'left') {
            return $onLeft($this->data['left']);
        }

        return $onRight($this->data['right']);
    }
}
