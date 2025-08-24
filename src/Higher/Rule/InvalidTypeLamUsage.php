<?php

declare(strict_types=1);

namespace Highstan\Higher\Rule;

use Highstan\Higher\WrappedTypeLam;
use PHPStan\Type\Type;

final readonly class InvalidTypeLamUsage
{
    public function __construct(
        public WrappedTypeLam $typeLam,
        public Type $parentType,
    ) {}
}
