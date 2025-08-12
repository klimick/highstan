<?php

declare(strict_types=1);

namespace Highstan;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\Type;

final readonly class NormalizeHKExpression implements ExpressionTypeResolverExtension
{
    private const string RecursionGuard = self::class . 'resolving-hk';

    public function getType(Expr $expr, Scope $scope): ?Type
    {
        if ($expr->getAttribute(self::RecursionGuard) === true) {
            return null;
        }

        $originalAttributes = $expr->getAttributes();
        $expr->setAttribute(self::RecursionGuard, true);

        $originalType = $scope->getType($expr);
        $expr->setAttributes($originalAttributes);

        return HKTypeNormalizer::normalize($originalType);
    }
}
