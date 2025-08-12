<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\HK;

/**
 * @implements Exp<int>
 */
final readonly class Num implements Exp
{
    public function __construct(
        public int $value,
    ) {}

    public function accept(ExpVisitor $visitor): HK
    {
        return $visitor->visitNum($this);
    }
}
