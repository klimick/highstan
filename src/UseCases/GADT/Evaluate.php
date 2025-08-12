<?php

declare(strict_types=1);

namespace Highstan\UseCases\GADT;

use Highstan\HKEncoding\HK;

/**
 * @template A
 * @implements HK<EvaluateTypeLambda, A>
 */
final readonly class Evaluate implements HK
{
    /**
     * @param \Closure(): A $evaluate
     */
    public function __construct(
        private \Closure $evaluate,
    ) {}

    /**
     * @return A
     */
    public function run(): mixed
    {
        return ($this->evaluate)();
    }
}
