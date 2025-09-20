<?php

declare(strict_types=1);

namespace Highstan\UseCases\TaglessFinal;

/**
 * @implements ExprSemV1<type-lam(x): string>
 */
enum ExprStringifierV1 implements ExprSemV1
{
    case Semantics;

    public function num(int $value): string
    {
        return "{$value}";
    }

    public function add(mixed $left, mixed $right): string
    {
        return "({$left} + {$right})";
    }
}
