<?php

declare(strict_types=1);

namespace Highstan\Higher;

use PHPStan\Type\Type;

trait NoTraverseTrait
{
    public function traverse(callable $cb): self
    {
        return $this;
    }

    public function traverseSimultaneously(Type $right, callable $cb): Type
    {
        return $this;
    }
}
