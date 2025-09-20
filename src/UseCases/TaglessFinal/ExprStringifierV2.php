<?php

declare(strict_types=1);

namespace Highstan\UseCases\TaglessFinal;

/**
 * @implements ExprSemV2<type-lam(x): string>
 */
enum ExprStringifierV2 implements ExprSemV2
{
    case Semantics;

    public function eq(mixed $left, mixed $right): string
    {
        return "({$left} === {$right})";
    }
}
