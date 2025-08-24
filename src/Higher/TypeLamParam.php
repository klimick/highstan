<?php

declare(strict_types=1);

namespace Highstan\Higher;

use PHPStan\Type\IsSuperTypeOfResult;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class TypeLamParam extends MixedType
{
    use NoAcceptTrait;
    use NoSubtractionTrait;
    use NoTraverseTrait;

    public function __construct(
        public string $name,
        public Type $upperBound,
        public TypeLamParamVariance $variance,
    ) {
        // With `isExplicitMixed: false`
        // PHPStan emits an error like this: https://phpstan.org/r/cbd31de0-78ae-40f9-9797-9b8d7007b8b0
        parent::__construct(isExplicitMixed: true);
    }

    /**
     * @param callable(Type): Type $traverse
     */
    public static function resolveToBounds(Type $type, callable $traverse): Type
    {
        return $type instanceof self ? $traverse($type->upperBound) : $traverse($type);
    }

    public function describe(VerbosityLevel $level): string
    {
        return $this->name;
    }

    /**
     * The key method for type-lam params normalization:
     *
     * type-lam(x): x|x                               ->   type-lam(x): x
     * type-lam(x, y): x|y                            ->   type-lam(x, y): x|y
     * type-lam(x, y): x&y                            ->   type-lam(x, y): x&y
     * type-lam<x of int, y of string>(x, y): x & y   ->   type-lam<x of int, y of string>(x, y): never
     * type-lam(x): x|int                             ->   type-lam(x): never
     * type-lam(x): x&int                             ->   type-lam(x): never
     */
    public function isSuperTypeOf(Type $type): IsSuperTypeOfResult
    {
        if (!$type instanceof self) {
            return IsSuperTypeOfResult::createNo();
        }

        if ($this->equals($type)) {
            return IsSuperTypeOfResult::createYes();
        }

        return $this->upperBound->isSuperTypeOf($type->upperBound)->yes()
            ? IsSuperTypeOfResult::createMaybe()
            : IsSuperTypeOfResult::createNo();
    }

    public function isSuperTypeOfMixed(MixedType $type): IsSuperTypeOfResult
    {
        return $this->isSuperTypeOf($type);
    }

    public function equals(Type $type): bool
    {
        return $type instanceof self
            && $type->name === $this->name
            && $type->upperBound->equals($this->upperBound)
            && $type->variance === $this->variance;
    }

    // Checking type compatibility in @-extends/@-implements
    public function isSubTypeOf(Type $otherType): IsSuperTypeOfResult
    {
        return $otherType->isSuperTypeOf($this->upperBound);
    }
}
