<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

/**
 * @template A
 */
interface Exp
{
    /**
     * @template R of type-lam<_>
     *
     * @param ExpVisitor<R> $visitor
     * @return R<A>
     */
    public function accept(ExpVisitor $visitor): mixed;
}
