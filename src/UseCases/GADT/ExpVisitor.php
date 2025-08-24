<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

/**
 * @template R of type-lam<_>
 */
interface ExpVisitor
{
    /**
     * @return R<int>
     */
    public function visitNum(Num $exp): mixed;

    /**
     * @return R<int>
     */
    public function visitAdd(Add $exp): mixed;

    /**
     * @return R<bool>
     */
    public function visitEq(Eq $exp): mixed;
}
