<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\HK;

/**
 * @implements ExpVisitor<ExpTypeLambda>
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
        $cloned = $exp->accept(new self());

        return ExpTypeLambda::fix($cloned);
    }

    /**
     * @return HK<ExpTypeLambda, int>
     */
    public function visitNum(Num $exp): HK
    {
        return new Num($exp->value);
    }

    /**
     * @return HK<ExpTypeLambda, int>
     */
    public function visitAdd(Add $exp): HK
    {
        return new Add(
            left: self::clone($exp->left),
            right: self::clone($exp->right),
        );
    }

    /**
     * @return HK<ExpTypeLambda, bool>
     */
    public function visitEq(Eq $exp): HK
    {
        return new Eq(
            left: self::clone($exp->left),
            right: self::clone($exp->right),
        );
    }
}
