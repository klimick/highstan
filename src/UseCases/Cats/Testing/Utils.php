<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Testing;

final readonly class Utils
{
    public static function intToString(int $value): string
    {
        return "{$value}";
    }
}
