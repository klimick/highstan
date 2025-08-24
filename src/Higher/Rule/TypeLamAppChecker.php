<?php

declare(strict_types=1);

namespace Highstan\Higher\Rule;

use Highstan\Higher\TypeLamApp;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final readonly class TypeLamAppChecker
{
    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    public function check(Type $type, int $atLine): \Generator
    {
        foreach (TypeCollector::collect(TypeLamApp::class, $type) as $typeLamApp) {
            yield from $this->checkTypeLamApp($typeLamApp, $atLine);
        }
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function checkTypeLamApp(TypeLamApp $typeLamApp, int $atLine): \Generator
    {
        $typeLam = $typeLamApp->getTypeLamBound();

        $typeParameters = $typeLam->params;
        $typeArguments = $typeLamApp->getParams();

        $typeParametersCount = \count($typeParameters);
        $typeArgumentsCount = \count($typeArguments);

        if ($typeParametersCount > $typeArgumentsCount) {
            yield RuleErrorBuilder::message(\sprintf(
                '%s has less params than expected',
                $typeLamApp->describe(VerbosityLevel::precise()),
            ))->identifier('generics.lessTypes')->line($atLine)->build();
        }

        if ($typeParametersCount < $typeArgumentsCount) {
            yield RuleErrorBuilder::message(\sprintf(
                '%s has more params than expected',
                $typeLamApp->describe(VerbosityLevel::precise()),
            ))->identifier('generics.moreTypes')->line($atLine)->build();
        }

        foreach ($typeArguments as $i => $typeArgument) {
            if (!\array_key_exists($i, $typeParameters)) {
                continue;
            }

            $typeParameter = $typeParameters[$i];

            if ($typeParameter->upperBound->isSuperTypeOf($typeArgument)->yes()) {
                continue;
            }

            yield RuleErrorBuilder::message(\sprintf(
                '%s is not subtype of %s',
                $typeArgument->describe(VerbosityLevel::precise()),
                $typeParameter->upperBound->describe(VerbosityLevel::precise()),
            ))->identifier('generics.notSubtype')->line($atLine)->build();
        }
    }
}
