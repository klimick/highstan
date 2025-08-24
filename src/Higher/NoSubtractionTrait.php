<?php

declare(strict_types=1);

namespace Highstan\Higher;

use PHPStan\Type\Type;

trait NoSubtractionTrait
{
    public function subtract(Type $type): self
    {
        return $this;
    }

    public function getSubtractedType(): null
    {
        return null;
    }

    public function getTypeWithoutSubtractedType(): self
    {
        return $this;
    }

    public function changeSubtractedType(?Type $subtractedType): self
    {
        return $this;
    }
}
