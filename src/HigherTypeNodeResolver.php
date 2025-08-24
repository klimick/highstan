<?php

declare(strict_types=1);

namespace Highstan;

use Highstan\Higher\TypeLam;
use Highstan\Higher\TypeLamApp;
use Highstan\Higher\TypeLamParam;
use Highstan\Higher\TypeLamParamVariance;
use Highstan\Higher\WrappedTypeLam;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\Tag\TemplateTag;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDoc\TypeNodeResolverAwareExtension;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeParameterNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

/**
 * @phpstan-type TypeLamParamsStack = \SplStack<array<string, TypeLamParam>>
 */
final class HigherTypeNodeResolver implements TypeNodeResolverExtension, TypeNodeResolverAwareExtension
{
    private ?TypeNodeResolver $typeNodeResolver = null;

    /** @phpstan-var TypeLamParamsStack */
    private \SplStack $typeLamParamsStack;

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {
        $this->typeLamParamsStack = new \SplStack();
    }

    public function setTypeNodeResolver(TypeNodeResolver $typeNodeResolver): void
    {
        $this->typeNodeResolver = $typeNodeResolver;
    }

    public function resolve(TypeNode $typeNode, NameScope $nameScope): ?Type
    {
        if ($this->typeNodeResolver === null) {
            return null;
        }

        // todo: If we inside type-lam return type then forbid:
        //  value-of<*>
        //  key-of<*>
        //  new<*>
        //  A[K]
        //  template-type<*, *, *>
        //  (x is y ? a : b)

        return self::resolveTypeLamFromClass($typeNode, $nameScope, $this->reflectionProvider)
            ?? self::resolveTypeLamApp($typeNode, $nameScope, $this->typeNodeResolver)
            ?? self::resolveTypeLamSyntaxSugar($typeNode, $nameScope, $this->typeNodeResolver)
            ?? self::resolveTypeLam($typeNode, $nameScope, $this->typeNodeResolver, $this->typeLamParamsStack)
            ?? self::resolveTypeLamParam($typeNode, $this->typeLamParamsStack);
    }

    private static function resolveTypeLamFromClass(
        TypeNode $typeNode,
        NameScope $nameScope,
        ReflectionProvider $reflectionProvider,
    ): ?Type {
        if (!$typeNode instanceof ConstTypeNode) {
            return null;
        }

        if (!$typeNode->constExpr instanceof ConstFetchNode) {
            return null;
        }

        if ($typeNode->constExpr->name !== 'type-lam') {
            return null;
        }

        $fullyQualifiedClassName = $nameScope->resolveStringName($typeNode->constExpr->className);

        if (!$reflectionProvider->hasClass($fullyQualifiedClassName)) {
            return null;
        }

        $classTemplates = $reflectionProvider
            ->getClass($fullyQualifiedClassName)
            ->getTemplateTags();

        if ($classTemplates === []) {
            return new ErrorType();
        }

        $typeLamParams = array_map(
            static fn(string $templateName, TemplateTag $templateTag) => new TypeLamParam(
                name: strtolower($templateName),
                upperBound: $templateTag->getBound(),
                variance: TypeLamParamVariance::fromClassTemplateTypeVariance($templateTag->getVariance()),
            ),
            array_keys($classTemplates),
            array_values($classTemplates),
        );

        $typeLamReturn = new GenericObjectType(
            mainType: $fullyQualifiedClassName,
            types: $typeLamParams,
        );

        return new WrappedTypeLam(
            new TypeLam($typeLamParams, $typeLamReturn),
        );
    }

    private static function resolveTypeLamApp(
        TypeNode $typeNode,
        NameScope $nameScope,
        TypeNodeResolver $typeNodeResolver,
    ): ?TypeLamApp {
        if (!$typeNode instanceof GenericTypeNode) {
            return null;
        }

        $templateType = $nameScope->getTemplateTypeMap()->getType($typeNode->type->name);

        if (!$templateType instanceof TemplateType) {
            return null;
        }

        $templateTypeUpperBound = $templateType->getBound();

        if (!$templateTypeUpperBound instanceof WrappedTypeLam) {
            return null;
        }

        $resolvedParams = $typeNodeResolver->resolveMultiple($typeNode->genericTypes, $nameScope);

        return $resolvedParams !== []
            ? new TypeLamApp($templateType, $resolvedParams)
            : null;
    }

    private static function resolveTypeLamSyntaxSugar(
        TypeNode $typeNode,
        NameScope $nameScope,
        TypeNodeResolver $typeNodeResolver,
    ): ?Type {
        // (type-lam(x): mixed) to (type-lam<x of mixed>(x): mixed)
        if ($typeNode instanceof CallableTypeNode
            && $typeNode->identifier->name === 'type-lam'
            && $typeNode->templateTypes === []
        ) {
            if ($typeNode->parameters === []) {
                return null;
            }

            $desugaredTemplateNodes = [];

            foreach ($typeNode->parameters as $parameter) {
                $resolvedParam = self::resolveTypeLamParamVariance($parameter->type);

                if ($resolvedParam === null) {
                    return null;
                }

                $desugaredTemplateNodes[] = new TemplateTagValueNode(
                    name: $resolvedParam['name'],
                    bound: null,
                    description: '',
                    default: null,
                    lowerBound: null,
                );
            }

            $desugaredTypeLam = new CallableTypeNode(
                identifier: $typeNode->identifier,
                parameters: $typeNode->parameters,
                returnType: $typeNode->returnType,
                templateTypes: $desugaredTemplateNodes,
            );

            return $typeNodeResolver->resolve($desugaredTypeLam, $nameScope);
        }

        // type-lam<_> -> (type-lam<p0 of mixed>(p0): mixed)
        // type-lam<covariant _> -> (type-lam<p0 of mixed>(covariant<p0>): mixed)
        // type-lam<contravariant _> -> (type-lam<p0 of mixed>(contravariant<p0>): mixed)
        if ($typeNode instanceof GenericTypeNode
            && $typeNode->type->name === 'type-lam'
        ) {
            $generatedParams = [];

            foreach ($typeNode->genericTypes as $idx => $genericType) {
                if (!$genericType instanceof IdentifierTypeNode || $genericType->name !== '_') {
                    return null;
                }

                $generatedParams[] = ['name' => "p{$idx}", 'variance' => $typeNode->variances[$idx]];
            }

            $desugaredParameterNodes = [];
            $desugaredTemplateNodes = [];

            foreach ($generatedParams as $generatedParam) {
                $desugaredParamName = new IdentifierTypeNode($generatedParam['name']);

                $desugaredVariance = match ($generatedParam['variance']) {
                    'contravariant' => new GenericTypeNode(
                        type: new IdentifierTypeNode('contravariant'),
                        genericTypes: [$desugaredParamName],
                        variances: ['invariant'],
                    ),
                    'invariant' => $desugaredParamName,
                    'covariant' => new GenericTypeNode(
                        type: new IdentifierTypeNode('covariant'),
                        genericTypes: [$desugaredParamName],
                        variances: ['invariant'],
                    ),
                    default => null,
                };

                if ($desugaredVariance === null) {
                    return null;
                }

                $desugaredParameterNodes[] = new CallableTypeParameterNode(
                    type: $desugaredVariance,
                    isReference: false,
                    isVariadic: false,
                    parameterName: '',
                    isOptional: false,
                );

                $desugaredTemplateNodes[] = new TemplateTagValueNode(
                    name: $generatedParam['name'],
                    bound: null,
                    description: '',
                    default: null,
                    lowerBound: null,
                );
            }

            $desugaredTypeLam = new CallableTypeNode(
                identifier: $typeNode->type,
                parameters: $desugaredParameterNodes,
                returnType: new IdentifierTypeNode('mixed'),
                templateTypes: $desugaredTemplateNodes,
            );

            return $typeNodeResolver->resolve($desugaredTypeLam, $nameScope);
        }

        // type-lam-id -> (type-lam<p0 of mixed>(p0): mixed)
        if ($typeNode instanceof IdentifierTypeNode
            && $typeNode->name === 'type-lam-id'
        ) {
            $desugaredTypeLam = new CallableTypeNode(
                identifier: new IdentifierTypeNode('type-lam'),
                parameters: [
                    new CallableTypeParameterNode(
                        type: new IdentifierTypeNode('p0'),
                        isReference: false,
                        isVariadic: false,
                        parameterName: '',
                        isOptional: false,
                    ),
                ],
                returnType: new IdentifierTypeNode('p0'),
                templateTypes: [
                    new TemplateTagValueNode(
                        name: 'p0',
                        bound: null,
                        description: '',
                        default: null,
                        lowerBound: null,
                    ),
                ],
            );

            return $typeNodeResolver->resolve($desugaredTypeLam, $nameScope);
        }

        return null;
    }

    /**
     * @phpstan-param TypeLamParamsStack $typeLamParamsStack
     */
    private static function resolveTypeLam(
        TypeNode $typeNode,
        NameScope $nameScope,
        TypeNodeResolver $typeNodeResolver,
        \SplStack $typeLamParamsStack,
    ): null|WrappedTypeLam|ErrorType {
        if (!$typeNode instanceof CallableTypeNode) {
            return null;
        }

        if ($typeNode->identifier->name !== 'type-lam') {
            return null;
        }

        if ($typeNode->templateTypes === []) {
            return null;
        }

        if ($typeNode->parameters === []) {
            return null;
        }

        if (\count($typeNode->templateTypes) !== \count($typeNode->parameters)) {
            return null;
        }

        $typeLamUpperBounds = [];

        foreach ($typeNode->templateTypes as $templateType) {
            if ($templateType->default !== null) {
                return null; // does not support
            }

            if ($templateType->lowerBound !== null) {
                return null; // does not support
            }

            $typeLamUpperBounds[$templateType->name] = $templateType->bound !== null
                ? $typeNodeResolver->resolve($templateType->bound, $nameScope)
                : new MixedType();
        }

        $definedTypeLamParams = [];

        foreach ($typeNode->parameters as $parameter) {
            if ($parameter->parameterName !== ''
                || $parameter->isVariadic
                || $parameter->isOptional
                || $parameter->isReference
            ) {
                return null; // does not have meaning
            }

            $resolved = self::resolveTypeLamParamVariance($parameter->type);

            if ($resolved === null) {
                return null;
            }

            if (!\array_key_exists($resolved['name'], $typeLamUpperBounds)) {
                return null;
            }

            $definedTypeLamParams[$resolved['name']] = new TypeLamParam(
                name: $resolved['name'],
                upperBound: $typeLamUpperBounds[$resolved['name']],
                variance: $resolved['variance'],
            );
        }

        if (\count($typeLamUpperBounds) !== \count($definedTypeLamParams)) {
            return null;
        }

        $typeLamParamsStack->push($definedTypeLamParams);
        $typeLamReturn = $typeNodeResolver->resolve($typeNode->returnType, $nameScope);
        $typeLamParamsStack->pop();

        if ($typeLamReturn instanceof ErrorType) {
            return $typeLamReturn;
        }

        return new WrappedTypeLam(
            typeLam: new TypeLam(
                params: array_values($definedTypeLamParams),
                return: $typeLamReturn,
            ),
        );
    }

    /**
     * @phpstan-param TypeLamParamsStack $typeLamParamsStack
     */
    private static function resolveTypeLamParam(
        TypeNode $typeNode,
        \SplStack $typeLamParamsStack,
    ): ?TypeLamParam {
        if (!$typeNode instanceof IdentifierTypeNode) {
            return null;
        }

        if ($typeLamParamsStack->isEmpty()) {
            return null;
        }

        $typeLamParams = $typeLamParamsStack->top();

        if (!\array_key_exists($typeNode->name, $typeLamParams)) {
            return null;
        }

        return $typeLamParams[$typeNode->name];
    }

    /**
     * @return ?array{
     *     name: non-empty-string,
     *     variance: TypeLamParamVariance,
     * }
     */
    private static function resolveTypeLamParamVariance(TypeNode $param): ?array
    {
        if ($param instanceof IdentifierTypeNode && $param->name !== '') {
            return [
                'name' => $param->name,
                'variance' => TypeLamParamVariance::Invariant,
            ];
        }

        if ($param instanceof GenericTypeNode
            && $param->type->name === 'covariant'
            && \count($param->genericTypes) === 1
            && $param->genericTypes[0] instanceof IdentifierTypeNode
            && $param->genericTypes[0]->name !== ''
            && $param->variances === ['invariant']
        ) {
            return [
                'name' => $param->genericTypes[0]->name,
                'variance' => TypeLamParamVariance::Covariant,
            ];
        }

        if ($param instanceof GenericTypeNode
            && $param->type->name === 'contravariant'
            && \count($param->genericTypes) === 1
            && $param->genericTypes[0] instanceof IdentifierTypeNode
            && $param->genericTypes[0]->name !== ''
            && $param->variances === ['invariant']
        ) {
            return [
                'name' => $param->genericTypes[0]->name,
                'variance' => TypeLamParamVariance::Contravariant,
            ];
        }

        return null;
    }
}
