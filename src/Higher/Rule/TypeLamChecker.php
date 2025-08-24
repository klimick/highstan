<?php

declare(strict_types=1);

namespace Highstan\Higher\Rule;

use Highstan\Higher\TypeLamParamVariance;
use Highstan\Higher\WrappedTypeLam;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final readonly class TypeLamChecker
{
    /**
     * @return list<IdentifierRuleError>
     */
    public function check(Type $type, int $atLine, bool $allowVariance = false): array
    {
        return [
            ...($allowVariance
                ? $this->checkVarianceSigns($type, $atLine)
                : $this->checkNoVarianceSigns($type, $atLine)),
            ...$this->detectInvalidUsages($type, $allowVariance, $atLine),
        ];
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function checkVarianceSigns(Type $type, int $atLine): \Generator
    {
        foreach (TypeCollector::collect(WrappedTypeLam::class, $type) as $wrappedTypeLam) {
            $typeLam = $wrappedTypeLam->unwrap();

            foreach (TypeLamParamRefCollector::collect($typeLam->return) as $ref) {
                if ($ref instanceof TypeLamParamRefUnsupported) {
                    yield RuleErrorBuilder::message(\sprintf(
                        'Use of type-lam param %s in this position is not supported yet.',
                        $ref->typeLamParam->name,
                    ))->identifier('typeLam.variancePosition')->line($atLine)->build();
                } elseif (!$ref->typeLamParam->variance->isValidPosition($ref->variance)) {
                    yield RuleErrorBuilder::message(\sprintf(
                        'type-lam param %s is declared as %s, but occurs in %s position.',
                        $ref->typeLamParam->name,
                        $ref->typeLamParam->variance->value,
                        $ref->variance->value,
                    ))->identifier('typeLam.variancePosition')->line($atLine)->build();
                }
            }
        }
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function checkNoVarianceSigns(Type $type, int $atLine): \Generator
    {
        foreach (TypeCollector::collect(WrappedTypeLam::class, $type) as $typeLam) {
            foreach ($typeLam->unwrap()->params as $param) {
                if ($param->variance !== TypeLamParamVariance::Invariant) {
                    yield RuleErrorBuilder::message(\sprintf(
                        'No variance annotation allowed in %s',
                        $typeLam->describe(VerbosityLevel::precise()),
                    ))->identifier('typeLam.varianceSign')->line($atLine)->build();
                }
            }
        }
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function detectInvalidUsages(Type $type, bool $isTypeLamAllowed, int $atLine): \Generator
    {
        $invalidTypeLamUsages = InvalidTypeLamUsagesCollector::collect(
            fromType: $type,
            parentType: $type,
            isTypeLamAllowed: $isTypeLamAllowed,
        );

        foreach ($invalidTypeLamUsages as $invalidTypeLamUsage) {
            if ($invalidTypeLamUsage->typeLam === $invalidTypeLamUsage->parentType) {
                yield RuleErrorBuilder::message(\sprintf(
                    '%s cannot be used as standalone type',
                    $invalidTypeLamUsage->typeLam->describe(VerbosityLevel::precise()),
                ))->identifier('typeLam.standaloneType')->line($atLine)->build();
            } else {
                yield RuleErrorBuilder::message(\sprintf(
                    '%s cannot be used in type %s',
                    $invalidTypeLamUsage->typeLam->describe(VerbosityLevel::precise()),
                    $invalidTypeLamUsage->parentType->describe(VerbosityLevel::precise()),
                ))->identifier('typeLam.standaloneType')->line($atLine)->build();
            }
        }
    }
}
