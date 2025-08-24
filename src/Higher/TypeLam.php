<?php

declare(strict_types=1);

namespace Highstan\Higher;

use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeVariance;
use PHPStan\Type\IsSuperTypeOfResult;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\SimultaneousTypeTraverser;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\VerbosityLevel;

final class TypeLam extends ObjectWithoutClassType
{
    use NoAcceptTrait;
    use NoSubtractionTrait;

    /**
     * @param non-empty-list<TypeLamParam> $params
     */
    public function __construct(
        public array $params,
        public Type $return,
    ) {
        parent::__construct();
    }

    public function getReferencedTemplateTypes(TemplateTypeVariance $positionVariance): array
    {
        return $this->return->getReferencedTemplateTypes($positionVariance);
    }

    public function traverse(callable $cb): self
    {
        $changed = false;
        $traversedParams = [];

        foreach ($this->params as $param) {
            $traversedUpperBound = $cb($param->upperBound);

            if ($traversedUpperBound === $param->upperBound) {
                $traversedParams[] = $param;

                continue;
            }

            $traversedParams[] = new TypeLamParam(
                name: $param->name,
                upperBound: $traversedUpperBound,
                variance: $param->variance,
            );

            $changed = true;
        }

        $traversedReturn = $cb($this->return);

        if ($traversedReturn !== $this->return) {
            $changed = true;

            // PHPStan has a strange behavior.
            // It turns everything that is a subtype of MixedType into StrictMixedType.
            // Since TypeLamParam extends from MixedType, it’s affected as well.
            // This workaround restores all replaced TypeLamParam back in place.
            $traversedReturn = SimultaneousTypeTraverser::map(
                left: $this->return,
                right: $traversedReturn,
                cb: static function (Type $left, Type $right, callable $traverse): Type {
                    if ($left instanceof TypeLamParam && !$right instanceof TypeLamParam) {
                        return $traverse($left, $left);
                    }

                    if ($left instanceof TemplateType && !$right instanceof TemplateType) {
                        return $traverse($right, $right);
                    }

                    return $traverse($left, $right);
                },
            );
        }

        return $changed
            ? new self($traversedParams, $traversedReturn)
            : $this;
    }

    public function equals(Type $type): bool
    {
        if ($type instanceof WrappedTypeLam) {
            $type = $type->unwrap();
        }

        return $type instanceof self
            && $this->isSuperTypeOf($type)->yes()
            && $type->isSuperTypeOf($this)->yes();
    }

    public function isSuperTypeOf(Type $type): IsSuperTypeOfResult
    {
        if (!$type instanceof self) {
            return IsSuperTypeOfResult::createNo();
        }

        // Subtyping for type-lambdas enforces *arity invariance*:
        // unlike anonymous functions (which may tolerate fewer parameters),
        // type-lambdas require an exact match in the number of parameters.
        if (\count($this->params) !== \count($type->params)) {
            return IsSuperTypeOfResult::createNo(['type-lam params count mismatch']);
        }

        $whyIsNotSuperType = [];

        // Parameter types are checked under *contravariance*:
        // each parameter type of the input type-lambda must be a supertype
        // of the corresponding parameter type in the current type-lambda.
        foreach ($this->params as $i => $currentParam) {
            $inputParam = $type->params[$i];

            if (!match ($currentParam->variance) {
                TypeLamParamVariance::Invariant => true,
                TypeLamParamVariance::Covariant => $inputParam->variance === TypeLamParamVariance::Covariant,
                TypeLamParamVariance::Contravariant => $inputParam->variance === TypeLamParamVariance::Contravariant,
            }) {
                $whyIsNotSuperType[] = \sprintf('->> Params %s and %s have incompatible variances', $currentParam->name, $inputParam->name);
            } elseif (!$inputParam->upperBound->isSuperTypeOf($currentParam->upperBound)->yes()) {
                $whyIsNotSuperType[] = \sprintf(
                    '->> Cannot substitute %s with %s: %s is not super type of %s',
                    $currentParam->name,
                    $inputParam->name,
                    $inputParam->upperBound->describe(VerbosityLevel::precise()),
                    $currentParam->upperBound->describe(VerbosityLevel::precise()),
                );
            }
        }

        $currentReturn = TypeTraverser::map($this->return, TypeLamParam::resolveToBounds(...));
        $inputReturn = TypeTraverser::map($type->return, TypeLamParam::resolveToBounds(...));

        // Return types are checked under *covariance*:
        // the return type of the current type-lambda must be a supertype
        // of the return type of the input type-lambda.
        if (!$currentReturn->isSuperTypeOf($inputReturn)->yes()) {
            $whyIsNotSuperType[] = 'Incompatible return types';
        }

        return $whyIsNotSuperType !== []
            ? IsSuperTypeOfResult::createNo($whyIsNotSuperType)
            : IsSuperTypeOfResult::createYes();
    }

    public function describe(VerbosityLevel $level): string
    {
        return \sprintf(
            'type-lam<%s>(%s): %s',
            implode(', ', array_map(
                static fn(TypeLamParam $p) => \sprintf('%s of %s', $p->name, $p->upperBound->describe($level)),
                $this->params,
            )),
            implode(', ', array_map(
                static fn(TypeLamParam $p) => match ($p->variance) {
                    TypeLamParamVariance::Invariant => $p->name,
                    TypeLamParamVariance::Covariant => \sprintf('covariant<%s>', $p->name),
                    TypeLamParamVariance::Contravariant => \sprintf('contravariant<%s>', $p->name),
                },
                $this->params,
            )),
            $this->return->describe($level),
        );
    }
}
