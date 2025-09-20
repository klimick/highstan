<?php

declare(strict_types=1);

namespace Highstan\UseCases\TaglessFinal;

/**
 * @template R of type-lam<_>
 */
interface ExprSemV1
{
    /**
     * @return R<int>
     */
    public function num(int $value): mixed;

    /**
     * @param R<int> $left
     * @param R<int> $right
     * @return R<int>
     */
    public function add(mixed $left, mixed $right): mixed;
}
