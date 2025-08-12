<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

/**
 * @template R of TypeLambda
 */
interface ExpVisitor
{
    /**
     * @return HK<R, int>
     */
    public function visitNum(Num $exp): mixed;

    /**
     * @return HK<R, int>
     */
    public function visitAdd(Add $exp): mixed;

    /**
     * @return HK<R, bool>
     */
    public function visitEq(Eq $exp): mixed;
}
