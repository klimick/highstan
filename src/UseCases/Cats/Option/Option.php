<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Option;

use Highstan\UseCases\Cats\TypeClass\Applicative;

/**
 * @template-covariant A = never
 */
final readonly class Option
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
     * @template G of type-lam<_>
     * @template B
     *
     * @param Applicative<G> $G
     * @param callable(A): G<B> $ab
     * @return G<Option<B>>
     */
    public function traverse(Applicative $G, callable $ab): mixed
    {
        if ($this->data['type'] === 'none') {
            /**
             * return.type: Issues with variance. G<Option<never>> =!= G<Option<B>>.
             * @phpstan-ignore return.type
             */
            return $G->pure(self::none());
        }

        /**
         * argument.type: PHPStan has poor support for polymorphic functions.
         * @phpstan-ignore argument.type
         */
        return $G->map($ab($this->data['some']), self::some(...));
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
