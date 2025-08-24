<?php

declare(strict_types=1);

namespace Highstan\Higher\Rule\TypeLamAppChecker;

use Highstan\Higher\Rule\TypeLamAppChecker;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InFunctionNode;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;

/**
 * @implements Rule<InFunctionNode>
 */
final readonly class TypeLamAppCheckerInFunctionNode implements Rule
{
    public function __construct(
        private TypeLamAppChecker $typeLamAppChecker,
    ) {}

    public function getNodeType(): string
    {
        return InFunctionNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return [
            ...$this->check($node->getFunctionReflection(), $node->getLine()),
        ];
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function check(FunctionReflection $functionReflection, int $functionNodeLine): \Generator
    {
        foreach ($functionReflection->getVariants() as $variant) {
            foreach ($variant->getTemplateTypeMap()->getTypes() as $templateType) {
                yield from $this->typeLamAppChecker->check(
                    type: $templateType,
                    atLine: $functionNodeLine,
                );
            }

            foreach ($variant->getParameters() as $parameter) {
                yield from $this->typeLamAppChecker->check(
                    type: $parameter->getPhpDocType(),
                    atLine: $functionNodeLine,
                );
            }

            yield from $this->typeLamAppChecker->check(
                type: $variant->getReturnType(),
                atLine: $functionNodeLine,
            );
        }
    }
}
