<?php

declare(strict_types=1);

namespace Highstan\Higher;

use PHPStan\Type\AcceptsResult;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IsSuperTypeOfResult;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

const __mixed_type__ = new MixedType();

/**
 * PHPStan does not allow using custom types as template bounds.
 * This class is a workaround that makes it possible to overcome this limitation.
 */
final class WrappedTypeLam extends IntersectionType
{
    public function __construct(
        private readonly TypeLam $typeLam,
    ) {
        parent::__construct([__mixed_type__, $typeLam]);
    }

    public function unwrap(): TypeLam
    {
        return $this->typeLam;
    }

    public function traverse(callable $cb): self
    {
        $traversedTypeLam = $this->typeLam->traverse($cb);

        return $traversedTypeLam !== $this->typeLam
            ? new self($traversedTypeLam)
            : $this;
    }

    public function equals(Type $type): bool
    {
        return match (true) {
            $type instanceof self => $this->typeLam->equals($type->typeLam),
            $type instanceof TypeLam => $this->typeLam->equals($type),
            default => false,
        };
    }

    public function isSuperTypeOf(Type $otherType): IsSuperTypeOfResult
    {
        return match (true) {
            $otherType instanceof self => $this->typeLam->isSuperTypeOf($otherType->typeLam),
            $otherType instanceof TypeLam => $this->typeLam->isSuperTypeOf($otherType),
            $otherType instanceof TypeLamParam => $this->isSuperTypeOf($otherType->upperBound),
            default => IsSuperTypeOfResult::createNo(),
        };
    }

    public function isSubTypeOf(Type $otherType): IsSuperTypeOfResult
    {
        return IsSuperTypeOfResult::createNo();
    }

    public function accepts(Type $otherType, bool $strictTypes): AcceptsResult
    {
        return AcceptsResult::createNo();
    }

    public function isAcceptedBy(Type $acceptingType, bool $strictTypes): AcceptsResult
    {
        return AcceptsResult::createNo();
    }

    public function describe(VerbosityLevel $level): string
    {
        return $this->typeLam->describe($level);
    }
}
