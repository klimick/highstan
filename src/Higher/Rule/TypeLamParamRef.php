<?php

declare(strict_types=1);

namespace Highstan\Higher\Rule;

use Highstan\Higher\TypeLamParam;
use Highstan\Higher\TypeLamParamVariance;

final readonly class TypeLamParamRef
{
    public function __construct(
        public TypeLamParam $typeLamParam,
        public TypeLamParamVariance $variance,
    ) {}
}
