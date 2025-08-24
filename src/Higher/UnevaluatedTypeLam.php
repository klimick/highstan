<?php

declare(strict_types=1);

namespace Highstan\Higher;

use PHPStan\TrinaryLogic;
use PHPStan\Type\AcceptsResult;
use PHPStan\Type\IsSuperTypeOfResult;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class UnevaluatedTypeLam extends ObjectWithoutClassType
{
    use NoSubtractionTrait;
    use NoTraverseTrait;

    /**
     * @param list<string> $reasons
     */
    public function __construct(
        private readonly TypeLam $unevaluatedTypeLam,
        private readonly array $reasons,
    ) {
        parent::__construct();
    }

    public function getUnevaluatedTypeLam(): TypeLam
    {
        return $this->unevaluatedTypeLam;
    }

    /**
     * @return list<string>
     */
    public function getReasons(): array
    {
        return $this->reasons;
    }

    /**
     * @param list<string> $reasons
     */
    public static function cannotSubstitute(TypeLam $unevaluated, TypeLam $concrete, array $reasons): self
    {
        return new self(
            unevaluatedTypeLam: $unevaluated,
            reasons: [
                \sprintf(
                    '-> Cannot substitute %s with %s',
                    $unevaluated->describe(VerbosityLevel::precise()),
                    $concrete->describe(VerbosityLevel::precise()),
                ),
                ...$reasons,
            ],
        );
    }

    public static function isNotSubtype(TypeLamApp $unevaluated, Type $receivedTypeAsTypeLamApp): self
    {
        return new self(
            unevaluatedTypeLam: $unevaluated->getTypeLamBound(),
            reasons: [
                \sprintf(
                    'Type %s is not subtype of %s',
                    $receivedTypeAsTypeLamApp->describe(VerbosityLevel::precise()),
                    $unevaluated->describe(VerbosityLevel::precise()),
                ),
            ],
        );
    }

    public function describe(VerbosityLevel $level): string
    {
        return \sprintf('unevaluated(%s)', $this->unevaluatedTypeLam->describe($level));
    }

    public function hasMethod(string $methodName): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function hasProperty(string $propertyName): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function equals(Type $type): bool
    {
        return false;
    }

    public function isSuperTypeOf(Type $type): IsSuperTypeOfResult
    {
        return IsSuperTypeOfResult::createNo();
    }

    public function accepts(Type $type, bool $strictTypes): AcceptsResult
    {
        return AcceptsResult::createNo($this->reasons);
    }
}
