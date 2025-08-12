<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

/**
 * @implements Exp<bool>
 */
final readonly class Eq implements Exp
{
    /**
     * @param Exp<int> $left
     * @param Exp<int> $right
     */
    public function __construct(
        public Exp $left,
        public Exp $right,
    ) {}

    /**
     * @template R of TypeLambda
     *
     * @param ExpVisitor<R> $visitor
     * @return HK<R, bool>
     */
    public function accept(ExpVisitor $visitor): mixed
    {
        return $visitor->visitEq($this);
    }
}
