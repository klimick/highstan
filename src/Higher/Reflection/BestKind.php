<?php

declare(strict_types=1);

namespace Highstan\Higher\Reflection;

use Highstan\Higher\TypeLamParamVariance;
use PHPStan\Type\Type;

final readonly class BestKind
{
    /**
     * @param non-empty-list<array{
     *     name: string,
     *     upperBound: Type,
     *     typeArgument: Type,
     *     variance: TypeLamParamVariance,
     * }> $templates
     */
    public function __construct(
        public string $typename,
        public array $templates,
        public int $leftParamsCount,
    ) {}
}
