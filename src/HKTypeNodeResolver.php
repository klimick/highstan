<?php

declare(strict_types=1);

namespace Highstan;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDoc\TypeNodeResolverAwareExtension;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeVariance;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

final class HKTypeNodeResolver implements TypeNodeResolverExtension, TypeNodeResolverAwareExtension
{
    private ?TypeNodeResolver $typeNodeResolver = null;

    public function setTypeNodeResolver(TypeNodeResolver $typeNodeResolver): void
    {
        $this->typeNodeResolver = $typeNodeResolver;
    }

    public function resolve(TypeNode $typeNode, NameScope $nameScope): ?Type
    {
        if ($this->typeNodeResolver === null) {
            return null;
        }

        return self::resolveHKType($this->typeNodeResolver, $typeNode, $nameScope);
    }

    private static function resolveHKType(TypeNodeResolver $typeNodeResolver, TypeNode $typeNode, NameScope $nameScope): ?Type
    {
        if (!$typeNode instanceof GenericTypeNode) {
            return null;
        }

        $typeLambdaT = $nameScope
            ->getTemplateTypeMap()
            ->getType($typeNode->type->name);

        if (!$typeLambdaT instanceof TemplateType) {
            return null;
        }

        $isTypeLambda = (new ObjectType(TypeLambda::class))
            ->isSuperTypeOf($typeLambdaT->getBound())
            ->yes();

        if (!$isTypeLambda) {
            return null;
        }

        // Constructs like F<covariant A> are forbidden due to unsoundness.
        foreach ($typeNode->variances as $variance) {
            if ($variance !== 'invariant') {
                return new ErrorType();
            }
        }

        return new GenericObjectType(
            mainType: HK::class,
            types: [
                $typeLambdaT,
                $typeNodeResolver->resolve($typeNode->genericTypes[0], $nameScope),
            ],
            variances: [
                TemplateTypeVariance::createInvariant(),
                TemplateTypeVariance::createInvariant(),
            ],
        );
    }
}
