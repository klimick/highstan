<?php

declare(strict_types=1);

namespace Highstan\Higher;

use PHPStan\Type\Generic\TemplateTypeVariance;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

enum TypeLamParamVariance: string
{
    case Contravariant = 'contravariant';
    case Invariant = 'invariant';
    case Covariant = 'covariant';

    public static function fromClassTemplateTypeVariance(TemplateTypeVariance $variance): self
    {
        return match (true) {
            $variance->invariant() => self::Invariant,
            $variance->covariant() => self::Covariant,
            $variance->contravariant() => self::Contravariant,
            default => throw new \RuntimeException('Class @template variance can be only invariant, covariant or contravariant'),
        };
    }

    public function compose(self $other): self
    {
        if ($this === self::Contravariant) {
            return match ($other) {
                self::Contravariant => self::Covariant,
                self::Covariant => self::Contravariant,
                default => self::Invariant,
            };
        }

        if ($this === self::Covariant) {
            return match ($other) {
                self::Contravariant => self::Contravariant,
                self::Covariant => self::Covariant,
                default => self::Invariant,
            };
        }

        return self::Invariant;
    }

    public function isValidVariance(Type $a, Type $b): bool
    {
        return match ($this) {
            TypeLamParamVariance::Contravariant => $a->isSuperTypeOf($b)->yes(),
            TypeLamParamVariance::Invariant => $b->equals($a),
            TypeLamParamVariance::Covariant => $b->isSuperTypeOf($a)->yes(),
        };
    }

    public function getInvalidVarianceMessage(Type $a, Type $b): string
    {
        return match ($this) {
            TypeLamParamVariance::Contravariant => \sprintf(
                'Type %s is not contravariant to %s',
                $a->describe(VerbosityLevel::precise()),
                $b->describe(VerbosityLevel::precise()),
            ),
            TypeLamParamVariance::Invariant => \sprintf(
                'Type %s is not equals to %s',
                $a->describe(VerbosityLevel::precise()),
                $b->describe(VerbosityLevel::precise()),
            ),
            TypeLamParamVariance::Covariant => \sprintf(
                'Type %s is not covariant to %s',
                $a->describe(VerbosityLevel::precise()),
                $b->describe(VerbosityLevel::precise()),
            ),
        };
    }

    public function isValidPosition(self $other): bool
    {
        return $this === $other || $this === self::Invariant;
    }

    public function toClassTemplateTypeVariance(): TemplateTypeVariance
    {
        return match ($this) {
            TypeLamParamVariance::Contravariant => TemplateTypeVariance::createContravariant(),
            TypeLamParamVariance::Invariant => TemplateTypeVariance::createInvariant(),
            TypeLamParamVariance::Covariant => TemplateTypeVariance::createCovariant(),
        };
    }
}
