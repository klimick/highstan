<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

/**
 * @implements ExpVisitor<type-lam(x): Exp<x>>
 */
final readonly class DeepCloner implements ExpVisitor
{
    /**
     * @template A
     *
     * @param Exp<A> $exp
     * @return Exp<A>
     */
    public static function clone(Exp $exp): Exp
    {
        return $exp->accept(new self());
    }

    /**
     * @return Exp<int>
     */
    public function visitNum(Num $exp): Exp
    {
        return new Num($exp->value);
    }

    /**
     * @return Exp<int>
     */
    public function visitAdd(Add $exp): Exp
    {
        return new Add(
            left: $exp->left->accept($this),
            right: $exp->right->accept($this),
        );
    }

    /**
     * @return Exp<bool>
     */
    public function visitEq(Eq $exp): Exp
    {
        return new Eq(
            left: $exp->left->accept($this),
            right: $exp->right->accept($this),
        );
    }
}
