<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Option;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;
use Highstan\UseCases\Cats\TypeClass\Applicative;

/**
 * @template A = never
 * @implements HK<OptionTypeLambda, A>
 */
final readonly class Option implements HK
{
    /**
     * @param ( array{type: 'some', some: A}
     *        | array{type: 'none'} ) $data
     */
    private function __construct(
        private array $data,
    ) {}

    /**
     * @return self<never>
     */
    public static function none(): self
    {
        return new self([
            'type' => 'none',
        ]);
    }

    /**
     * @template T
     *
     * @param T $value
     * @return self<T>
     */
    public static function some(mixed $value): self
    {
        return new self([
            'type' => 'some',
            'some' => $value,
        ]);
    }

    /**
     * @template B
     *
     * @param callable(A): B $ab
     * @return Option<B>
     */
    public function map(callable $ab): self
    {
        if ($this->data['type'] === 'none') {
            return self::none();
        }

        return self::some($ab($this->data['some']));
    }

    /**
     * @template B
     *
     * @param callable(A): Option<B> $ab
     * @return Option<B>
     */
    public function flatMap(callable $ab): self
    {
        if ($this->data['type'] === 'none') {
            return self::none();
        }

        return $ab($this->data['some']);
    }

    /**
     * @template B
     *
     * @param self<callable(A): B> $ab
     * @return Option<B>
     */
    public function apply(self $ab): self
    {
        if ($this->data['type'] === 'none') {
            return self::none();
        }

        if ($ab->data['type'] === 'none') {
            return self::none();
        }

        return self::some($ab->data['some']($this->data['some']));
    }

    /**
     * @template G of TypeLambda
     * @template B
     *
     * @param Applicative<G> $G
     * @param callable(A): HK<G, B> $ab
     * @return HK<G, Option<B>>
     */
    public function traverse(Applicative $G, callable $ab): HK
    {
        return $this->match(
            onNone: static fn() => $G->pure(Option::none()),
            /** @phpstan-ignore argument.type */
            onSome: static fn($a) => $G->map($ab($a), Option::some(...)),
        );
    }

    /**
     * @template NOut
     * @template SOut
     *
     * @param callable(): NOut $onNone
     * @param callable(A): SOut $onSome
     * @return NOut|SOut
     */
    public function match(callable $onNone, callable $onSome): mixed
    {
        if ($this->data['type'] === 'none') {
            return $onNone();
        }

        return $onSome($this->data['some']);
    }
}
