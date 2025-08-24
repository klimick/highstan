<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

/**
 * @implements ExpVisitor<type-lam(x): x>
 */
final readonly class Evaluator implements ExpVisitor
{
    /**
     * @template A
     *
     * @param Exp<A> $exp
     * @return A
     */
    public static function evaluate(Exp $exp): mixed
    {
        return $exp->accept(new self());
    }

    public function visitNum(Num $exp): int
    {
        return $exp->value;
    }

    public function visitAdd(Add $exp): int
    {
        $left = $exp->left->accept($this);
        $right = $exp->right->accept($this);

        return $left + $right;
    }

    public function visitEq(Eq $exp): bool
    {
        $left = $exp->left->accept($this);
        $right = $exp->right->accept($this);

        return $left === $right;
    }
}
