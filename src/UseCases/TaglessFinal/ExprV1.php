<?php

declare(strict_types=1);

namespace Highstan\UseCases\TaglessFinal;

/**
 * @template A
 */
final readonly class ExprV1
{
    /**
     * @param (\Closure<R of type-lam<_>>(ExprSemV1<R>): R<A>) $cont
     */
    public function __construct(
        public \Closure $cont,
    ) {}

    /**
     * @return ExprV1<int>
     */
    public static function num(int $value): self
    {
        return new self(static fn(ExprSemV1 $exprSem) => $exprSem->num($value));
    }

    /**
     * @param ExprV1<int> $left
     * @param ExprV1<int> $right
     * @return self<int>
     */
    public static function add(self $left, self $right): self
    {
        return new self(static fn(ExprSemV1 $exprSem) => $exprSem->add(
            left: ($left->cont)($exprSem),
            right: ($right->cont)($exprSem),
        ));
    }

    /**
     * @template R of type-lam<_>
     * @param ExprSemV1<R> $exprSem
     * @return R<A>
     */
    public function __invoke(ExprSemV1 $exprSem): mixed
    {
        return ($this->cont)($exprSem);
    }
}
