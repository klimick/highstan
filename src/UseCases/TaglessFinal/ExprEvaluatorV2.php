<?php

declare(strict_types=1);

namespace Highstan\UseCases\TaglessFinal;

/**
 * @implements ExprSemV2<type-lam(x): x>
 */
enum ExprEvaluatorV2 implements ExprSemV2
{
    case Semantics;

    public function eq(mixed $left, mixed $right): bool
    {
        return $left === $right;
    }
}
