<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

/**
 * @implements ExpVisitor<EvaluateTypeLambda>
 */
final readonly class Evaluator implements ExpVisitor
{
    /**
     * @template A
     *
     * @param Exp<A> $exp
     * @return Evaluate<A>
     */
    public static function evaluate(Exp $exp): Evaluate
    {
        return $exp->accept(new self());
    }

    /**
     * @return Evaluate<int>
     */
    public function visitNum(Num $exp): Evaluate
    {
        return new Evaluate(static fn() => $exp->value);
    }

    /**
     * @return Evaluate<int>
     */
    public function visitAdd(Add $exp): Evaluate
    {
        $left = $exp->left->accept($this);
        $right = $exp->right->accept($this);

        return new Evaluate(static fn() => $left() + $right());
    }

    /**
     * @return Evaluate<bool>
     */
    public function visitEq(Eq $exp): Evaluate
    {
        $left = $exp->left->accept($this);
        $right = $exp->right->accept($this);

        return new Evaluate(static fn() => $left() === $right());
    }
}
