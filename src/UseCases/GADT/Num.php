<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\TypeLambda;

/**
 * @implements Exp<int>
 */
final readonly class Num implements Exp
{
    public function __construct(
        public int $value,
    ) {}

    /**
     * @template R of TypeLambda
     *
     * @param ExpVisitor<R> $visitor
     * @return R<int>
     */
    public function accept(ExpVisitor $visitor): mixed
    {
        return $visitor->visitNum($this);
    }
}
