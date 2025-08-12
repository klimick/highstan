<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\HK;

/**
 * @implements Exp<int>
 */
final readonly class Add implements Exp
{
    /**
     * @param Exp<int> $left
     * @param Exp<int> $right
     */
    public function __construct(
        public Exp $left,
        public Exp $right,
    ) {}

    public function accept(ExpVisitor $visitor): HK
    {
        return $visitor->visitAdd($this);
    }
}
