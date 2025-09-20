<?php

declare(strict_types=1);

namespace Highstan\UseCases\TaglessFinal;

/**
 * @template A
 */
final readonly class ExprV2
{
    /**
     * @param (\Closure<R of type-lam<_>>(ExprSemV1<R>, ExprSemV2<R>): R<A>) $cont
     */
    public function __construct(
        public \Closure $cont,
    ) {}

    /**
     * @template X
     * @param ExprV1<X> $expr
     * @return ExprV2<X>
     */
    public static function lift(ExprV1 $expr): self
    {
        return new self(static fn(ExprSemV1 $semV1, ExprSemV2 $semV2) => $expr($semV1));
    }

    /**
     * @param ExprV2<int> $left
     * @param ExprV2<int> $right
     * @return ExprV2<bool>
     */
    public static function eq(self $left, self $right): self
    {
        return new self(static fn(ExprSemV1 $semV1, ExprSemV2 $semV2) => $semV2->eq(
            left: ($left->cont)($semV1, $semV2),
            right: ($right->cont)($semV1, $semV2),
        ));
    }

    /**
     * @template R of type-lam<_>
     * @param ExprSemV1<R> $semV1
     * @param ExprSemV2<R> $semV2
     * @return R<A>
     */
    public function __invoke(ExprSemV1 $semV1, ExprSemV2 $semV2): mixed
    {
        return ($this->cont)($semV1, $semV2);
    }
}
