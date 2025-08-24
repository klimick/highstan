<?php

declare(strict_types=1);

namespace Highstan\Higher\Reflection;

use Highstan\Higher\Rule\TypeCollector;
use Highstan\Higher\TypeLam;
use Highstan\Higher\TypeLamApp;
use Highstan\Higher\TypeLamParam;
use Highstan\Higher\TypeLamParamVariance;
use Highstan\Higher\UnevaluatedTypeLam;
use Highstan\Higher\WrappedTypeLam;
use PHPStan\Type\ErrorType;
use PHPStan\Type\GeneralizePrecision;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\VerbosityLevel;

final readonly class TypeLamEvaluator
{
    /**
     * @param non-empty-list<Type> $typeArguments
     */
    public static function tryToCompute(Type $typeLam, array $typeArguments, bool $withTemplateType = false): Type
    {
        if ($withTemplateType && $typeLam instanceof TemplateType && $typeLam->getBound() instanceof WrappedTypeLam) {
            return self::computeType($typeLam->getBound()->unwrap(), $typeArguments);
        }

        if ($typeLam instanceof WrappedTypeLam) {
            return self::computeType($typeLam->unwrap(), $typeArguments);
        }

        if ($typeLam instanceof TypeLam) {
            return self::computeType($typeLam, $typeArguments);
        }

        $unevaluatedTypeLams = TypeCollector::collect(UnevaluatedTypeLam::class, $typeLam);

        return $unevaluatedTypeLams === []
            ? self::generalizeInferredTypeArguments($typeLam, $typeArguments)
            : self::unevaluatedTypeLam($unevaluatedTypeLams);
    }

    /**
     * @param non-empty-list<Type> $typeArguments
     */
    private static function computeType(TypeLam $typeLam, array $typeArguments): Type
    {
        $inputTypeParamsCount = \count($typeArguments);
        $thisTypeParamsCount = \count($typeLam->params);

        if ($inputTypeParamsCount !== $thisTypeParamsCount) {
            return new ErrorType();
        }

        $typeParamsMap = [];

        foreach ($typeLam->params as $i => $param) {
            $typeParamsMap[$param->name] = $typeArguments[$i];
        }

        return TypeTraverser::map(
            $typeLam->return,
            static fn(Type $t, callable $traverse) => $t instanceof TypeLamParam && isset($typeParamsMap[$t->name])
                ? $typeParamsMap[$t->name]
                : $traverse($t),
        );
    }

    /**
     * @param non-empty-list<Type> $typeArguments
     */
    private static function generalizeInferredTypeArguments(Type $type, array $typeArguments): ErrorType|TypeLamApp
    {
        if ($type instanceof TemplateType && $type->getBound() instanceof WrappedTypeLam) {
            $typeLam = $type->getBound()->unwrap();
            $generalizedParams = [];

            foreach ($typeArguments as $i => $typeArgument) {
                $generalizedParams[] = \array_key_exists($i, $typeLam->params)
                    ? self::generalizeTypeLamParam($typeLam->params[$i], $typeArgument)
                    : $typeArgument;
            }

            return new TypeLamApp($type, $generalizedParams);
        }

        return new ErrorType();
    }

    private static function generalizeTypeLamParam(TypeLamParam $typeLamParam, Type $typeArgument): Type
    {
        if ($typeLamParam->variance !== TypeLamParamVariance::Covariant) {
            $isArrayKey = $typeLamParam->upperBound->describe(VerbosityLevel::precise()) === '(int|string)';

            if ($typeArgument->isScalar()->yes() && $isArrayKey) {
                return $typeArgument->generalize(GeneralizePrecision::templateArgument());
            }

            if ($typeArgument->isConstantValue()->yes() && (!$typeLamParam->upperBound->isScalar()->yes() || $isArrayKey)) {
                return $typeArgument->generalize(GeneralizePrecision::templateArgument());
            }
        }

        return $typeArgument;
    }

    /**
     * @param non-empty-list<UnevaluatedTypeLam> $unevaluatedTypeLams
     */
    public static function unevaluatedTypeLam(array $unevaluatedTypeLams): UnevaluatedTypeLam
    {
        $reasons = [];

        foreach ($unevaluatedTypeLams as $unevaluatedTypeLam) {
            foreach ($unevaluatedTypeLam->getReasons() as $reason) {
                $reasons[] = $reason;
            }
        }

        return new UnevaluatedTypeLam($unevaluatedTypeLams[0]->getUnevaluatedTypeLam(), $reasons);
    }
}
