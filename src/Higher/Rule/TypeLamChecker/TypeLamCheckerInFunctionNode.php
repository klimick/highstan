<?php

declare(strict_types=1);

namespace Highstan\Higher\Rule\TypeLamChecker;

use Highstan\Higher\Rule\TypeLamChecker;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InFunctionNode;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;

/**
 * @implements Rule<InFunctionNode>
 */
final readonly class TypeLamCheckerInFunctionNode implements Rule
{
    public function __construct(
        private TypeLamChecker $typeLamChecker,
    ) {}

    public function getNodeType(): string
    {
        return InFunctionNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $originalNode = $node->getOriginalNode();
        $functionReflection = $node->getFunctionReflection();

        return [
            ...$this->check($functionReflection, $originalNode->getLine()),
        ];
    }

    /**
     * @return \Generator<int, IdentifierRuleError>
     */
    private function check(FunctionReflection $functionReflection, int $functionNodeLine): \Generator
    {
        foreach ($functionReflection->getVariants() as $variant) {
            foreach ($variant->getTemplateTypeMap()->getTypes() as $templateType) {
                yield from $this->typeLamChecker->check(
                    type: $templateType,
                    atLine: $functionNodeLine,
                    allowVariance: true,
                );
            }

            foreach ($variant->getParameters() as $parameter) {
                yield from $this->typeLamChecker->check(
                    type: $parameter->getPhpDocType(),
                    atLine: $functionNodeLine,
                );
            }

            yield from $this->typeLamChecker->check(
                type: $variant->getReturnType(),
                atLine: $functionNodeLine,
            );
        }
    }
}
