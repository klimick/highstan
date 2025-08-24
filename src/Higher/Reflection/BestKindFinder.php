<?php

declare(strict_types=1);

namespace Highstan\Higher\Reflection;

use Highstan\Higher\TypeLamParamVariance;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;

final readonly class BestKindFinder
{
    public function __construct(
        private int $expectedTypeParamsCount,
    ) {}

    public function find(ClassReflection $classReflection): ?BestKind
    {
        $classParamsCount = \count($classReflection->getTemplateTypeMap()->getTypes());

        if ($classParamsCount >= $this->expectedTypeParamsCount) {
            return $this->mkBestKind($classReflection);
        }

        $parentReflection = $classReflection->getParentClass();

        if ($parentReflection !== null) {
            $parentParamsCount = \count($parentReflection->getTemplateTypeMap()->getTypes());

            if ($parentParamsCount >= $this->expectedTypeParamsCount) {
                return $this->mkBestKind($parentReflection);
            }
        }

        $interfacesWithTypeParams = [];

        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            $interfaceTypeParamsCount = \count($interfaceReflection->getTemplateTypeMap()->getTypes());

            if ($interfaceTypeParamsCount === 0) {
                continue;
            }

            $interfacesWithTypeParams[] = [$interfaceReflection, $interfaceTypeParamsCount];
        }

        // Find first interface with exact params count
        foreach ($interfacesWithTypeParams as [$interfaceReflection, $interfaceTypeParamsCount]) {
            if ($interfaceTypeParamsCount === $this->expectedTypeParamsCount) {
                return $this->mkBestKind($interfaceReflection);
            }
        }

        // Fallback to any first generic interface
        foreach ($interfacesWithTypeParams as [$interfaceReflection, $interfaceTypeParamsCount]) {
            if ($interfaceTypeParamsCount > $this->expectedTypeParamsCount) {
                return $this->mkBestKind($interfaceReflection);
            }
        }

        return null;
    }

    private function mkBestKind(ClassReflection $reflection): ?BestKind
    {
        $typeArguments = $reflection->getActiveTemplateTypeMap()->getTypes();
        $typeArgumentsCount = \count($typeArguments);

        if ($typeArgumentsCount === 0) {
            return null;
        }

        $upperBounds = $reflection
            ->getTemplateTypeMap()
            ->map(static fn($_, Type $templateType) => TypeTraverser::map(
                $templateType,
                static fn(Type $t, callable $traverse) => $t instanceof TemplateType
                    ? $traverse($t->getBound())
                    : $traverse($t),
            ))
            ->getTypes();

        if (\count($upperBounds) !== $typeArgumentsCount) {
            return null;
        }

        $variances = [];

        foreach ($reflection->getTemplateTags() as $templateTag) {
            $variances[$templateTag->getName()] = TypeLamParamVariance::fromClassTemplateTypeVariance($templateTag->getVariance());
        }

        if (\count($variances) !== $typeArgumentsCount) {
            return null;
        }

        return new BestKind(
            typename: $reflection->getName(),
            templates: array_map(
                static fn(string $templateName, Type $type) => [
                    'name' => $templateName,
                    'upperBound' => $upperBounds[$templateName],
                    'typeArgument' => $type,
                    'variance' => $variances[$templateName],
                ],
                array_keys($typeArguments),
                array_values($typeArguments),
            ),
            leftParamsCount: $typeArgumentsCount - $this->expectedTypeParamsCount,
        );
    }
}
