<?php

declare(strict_types=1);

namespace Highstan;

use Highstan\HKEncoding\HK;
use Highstan\HKEncoding\TypeLambda;
use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;

final readonly class HKTypeNormalizer
{
    public static function normalize(Type $type): Type
    {
        return TypeTraverser::map($type, static function (Type $potentiallyHKType, callable $traverse) {
            if ($potentiallyHKType->getObjectClassNames() !== [HK::class]) {
                return $traverse($potentiallyHKType);
            }

            $typeLambdaT = $potentiallyHKType->getTemplateType(HK::class, 'F');

            if ($typeLambdaT instanceof TemplateType) {
                $argsT = $potentiallyHKType->getTemplateType(HK::class, 'A');

                if (!$argsT instanceof TemplateType) {
                    return $traverse($potentiallyHKType);
                }
            }

            $isConcreteTypeLambda = (new ObjectType(TypeLambda::class))->isSuperTypeOf($typeLambdaT)->yes()
                && $typeLambdaT->hasMethod('fix')->yes();

            if (!$isConcreteTypeLambda) {
                return $potentiallyHKType;
            }

            $fixMethod = ParametersAcceptorSelector::selectFromTypes(
                types: [$potentiallyHKType],
                parametersAcceptors: $typeLambdaT
                    ->getMethod('fix', new OutOfClassScope())
                    ->getVariants(),
                unpack: false,
            );

            return $traverse($fixMethod->getReturnType());
        });
    }
}
