<?php

declare(strict_types=1);

namespace Highstan\UseCases\TaglessFinal;

/**
 * @implements ExprSemV1<type-lam(x): x>
 */
enum ExprEvaluatorV1 implements ExprSemV1
{
    case Semantics;

    public function num(int $value): int
    {
        return $value;
    }

    public function add(mixed $left, mixed $right): int
    {
        return $left + $right;
    }
}
