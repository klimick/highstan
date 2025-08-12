<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;

/**
 * @template A
 * @extends HK<ExpTypeLambda, A>
 */
interface Exp extends HK
{
    /**
     * @template R of TypeLambda
     *
     * @param ExpVisitor<R> $visitor
     * @return R<A>
     */
    public function accept(ExpVisitor $visitor): mixed;
}
