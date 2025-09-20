<?php

declare(strict_types=1);

namespace Highstan\UseCases\TaglessFinal;

/**
 * @template R of type-lam<_>
 */
interface ExprSemV2
{
    /**
     * @param R<int> $left
     * @param R<int> $right
     * @return R<bool>
     */
    public function eq(mixed $left, mixed $right): mixed;
}
