<?php

declare(strict_types=1);

namespace Highstan\Higher\Rule;

use Highstan\Higher\TypeLamParam;

final readonly class TypeLamParamRefUnsupported
{
    public function __construct(
        public TypeLamParam $typeLamParam,
    ) {}
}
