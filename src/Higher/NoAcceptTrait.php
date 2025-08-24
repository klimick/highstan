<?php

declare(strict_types=1);

namespace Highstan\Higher;

use PHPStan\Type\AcceptsResult;
use PHPStan\Type\Type;

trait NoAcceptTrait
{
    public function isAcceptedBy(Type $acceptingType, bool $strictTypes): AcceptsResult
    {
        // A type-lam param cannot be used as a standalone type.
        // It can only be part of another generic type.
        return AcceptsResult::createNo();
    }

    public function accepts(Type $type, bool $strictTypes): AcceptsResult
    {
        // A type-lam param cannot be used as a standalone type.
        // It can only be part of another generic type.
        return AcceptsResult::createNo();
    }
}
