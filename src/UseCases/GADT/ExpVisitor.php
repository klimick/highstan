<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\TypeLambda;

/**
 * @template R of TypeLambda
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
