<?php

declare(strict_types=1);

namespace Highstan\Higher\Rule\TypeLamAppChecker;

use Highstan\Higher\Rule\TypeLamAppChecker;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Type\Generic\GenericObjectType;

/**
 * @implements Rule<InClassNode>
 */
final readonly class TypeLamAppCheckerInClassNode implements Rule
{
    public function __construct(
        private TypeLamAppChecker $typeLamAppChecker,
    ) {}

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();
        $classNode = $node->getOriginalNode();
        $classNodeLine = $classNode->getLine();

        return [
            ...$this->checkImplementsTags($classReflection, $classNodeLine),
            ...$this->checkExtendsTags($classReflection, $classNodeLine),
            ...$this->checkTraitUses($classReflection, $classNode),
            ...$this->checkMagicMethods($classReflection, $classNodeLine),
            ...$this->checkMagicProperties($classReflection, $classNodeLine),
            ...$this->checkTemplates($classReflection, $classNodeLine),
            ...$this->checkNativeMethods($classReflection, $classNode, $scope),
            ...$this->checkNativeProperties($classReflection, $classNode, $scope),
        ];
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function checkImplementsTags(ClassReflection $classReflection, int $classNodeLine): \Generator
    {
        foreach ($classReflection->getImplementsTags() as $implementsTag) {
            yield from $this->typeLamAppChecker->check(
                type: $implementsTag->getType(),
                atLine: $classNodeLine,
            );
        }
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function checkExtendsTags(ClassReflection $classReflection, int $classNodeLine): \Generator
    {
        foreach ($classReflection->getExtendsTags() as $extendsTag) {
            yield from $this->typeLamAppChecker->check(
                type: $extendsTag->getType(),
                atLine: $classNodeLine,
            );
        }
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function checkTraitUses(ClassReflection $classReflection, ClassLike $classNode): \Generator
    {
        $traitUseLines = [];

        foreach ($classNode->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $traitUseLines[$trait->name] = $traitUse->getLine();
            }
        }

        foreach ($classReflection->getTraits() as $traitReflection) {
            $traitUseTypes = $traitReflection
                ->getTraitContextResolvedPhpDoc($classReflection)
                ?->getTemplateTypeMap()
                ->getTypes() ?? [];

            yield from $this->typeLamAppChecker->check(
                type: new GenericObjectType($traitReflection->getName(), array_values($traitUseTypes)),
                atLine: $traitUseLines[$traitReflection->getName()] ?? $classNode->getLine(),
            );
        }
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function checkMagicMethods(ClassReflection $classReflection, int $classNodeLine): \Generator
    {
        foreach ($classReflection->getMethodTags() as $methodTag) {
            foreach ($methodTag->getTemplateTags() as $templateTag) {
                yield from $this->typeLamAppChecker->check(
                    type: $templateTag->getBound(),
                    atLine: $classNodeLine,
                );
            }

            foreach ($methodTag->getParameters() as $parameter) {
                yield from $this->typeLamAppChecker->check(
                    type: $parameter->getType(),
                    atLine: $classNodeLine,
                );
            }

            yield from $this->typeLamAppChecker->check(
                type: $methodTag->getReturnType(),
                atLine: $classNodeLine,
            );
        }
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function checkMagicProperties(ClassReflection $classReflection, int $classNodeLine): \Generator
    {
        foreach ($classReflection->getPropertyTags() as $propertyTag) {
            $readableType = $propertyTag->getReadableType();

            if ($readableType !== null) {
                yield from $this->typeLamAppChecker->check(
                    type: $readableType,
                    atLine: $classNodeLine,
                );
            }

            $writableType = $propertyTag->getWritableType();

            if ($writableType !== null && $writableType !== $readableType) {
                yield from $this->typeLamAppChecker->check(
                    type: $writableType,
                    atLine: $classNodeLine,
                );
            }
        }
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function checkTemplates(ClassReflection $classReflection, int $classNodeLine): \Generator
    {
        foreach ($classReflection->getTemplateTypeMap()->getTypes() as $templateType) {
            yield from $this->typeLamAppChecker->check(
                type: $templateType,
                atLine: $classNodeLine,
            );
        }
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function checkNativeMethods(ClassReflection $classReflection, ClassLike $classNode, Scope $scope): \Generator
    {
        foreach ($classNode->getMethods() as $methodNode) {
            $methodReflection = $classReflection->getMethod($methodNode->name->toString(), $scope);
            $methodLineNumber = $methodNode->getLine();

            foreach ($methodReflection->getVariants() as $variant) {
                foreach ($variant->getTemplateTypeMap()->getTypes() as $templateType) {
                    yield from $this->typeLamAppChecker->check(
                        type: $templateType,
                        atLine: $methodLineNumber,
                    );
                }

                foreach ($variant->getParameters() as $parameter) {
                    yield from $this->typeLamAppChecker->check(
                        type: $parameter->getPhpDocType(),
                        atLine: $methodLineNumber,
                    );

                    $parameterOutType = $parameter->getOutType();

                    if ($parameterOutType !== null) {
                        yield from $this->typeLamAppChecker->check(
                            type: $parameterOutType,
                            atLine: $methodLineNumber,
                        );
                    }
                }

                yield from $this->typeLamAppChecker->check(
                    type: $variant->getPhpDocReturnType(),
                    atLine: $methodLineNumber,
                );
            }
        }
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function checkNativeProperties(ClassReflection $classReflection, ClassLike $classNode, Scope $scope): \Generator
    {
        foreach ($classNode->getProperties() as $propertyNode) {
            $propertyLineNumber = $propertyNode->getLine();

            foreach ($propertyNode->props as $propNode) {
                $propertyReflection = $classReflection->getProperty($propNode->name->toString(), $scope);

                yield from $this->typeLamAppChecker->check(
                    type: $propertyReflection->getPhpDocType(),
                    atLine: $propertyLineNumber,
                );
            }
        }
    }
}
