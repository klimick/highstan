<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\HK;

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
        $kind = $exp->accept(new self());

        return EvaluateTypeLambda::fix($kind);
    }

    /**
     * @return HK<EvaluateTypeLambda, int>
     */
    public function visitNum(Num $exp): HK
    {
        return new Evaluate(static fn() => $exp->value);
    }

    /**
     * @return HK<EvaluateTypeLambda, int>
     */
    public function visitAdd(Add $exp): HK
    {
        return new Evaluate(
            static fn() => self::evaluate($exp->left)->run() + self::evaluate($exp->right)->run(),
        );
    }

    /**
     * @return HK<EvaluateTypeLambda, bool>
     */
    public function visitEq(Eq $exp): HK
    {
        return new Evaluate(
            static fn() => self::evaluate($exp->left)->run() === self::evaluate($exp->right)->run(),
        );
    }
}
