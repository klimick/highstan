<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

/**
 * @implements Exp<int>
 */
final readonly class Add implements Exp
{
    /**
     * @param Exp<int> $left
     * @param Exp<int> $right
     */
    public function __construct(
        public Exp $left,
        public Exp $right,
    ) {}

    /**
     * @template R of type-lam<_>
     *
     * @param ExpVisitor<R> $visitor
     * @return R<int>
     */
    public function accept(ExpVisitor $visitor): mixed
    {
        return $visitor->visitAdd($this);
    }
}
