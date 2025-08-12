<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Testing;

use Highstan\HKEncoding\TypeLambda;
use Highstan\UseCases\Cats\Either\EitherInstance;
use Highstan\UseCases\Cats\Lst\LstInstance;
use Highstan\UseCases\Cats\Option\OptionInstance;
use Highstan\UseCases\Cats\TypeClass\Applicative;
use Highstan\UseCases\Cats\TypeClass\Foldable;

final readonly class FoldableUseCase
{
    /**
     * @template F of TypeLambda
     * @param Foldable<F> & Applicative<F> $F
     */
    public function fold(Foldable&Applicative $F): int
    {
        return $F->fold(
            reducer: static fn(int $a, int $b) => $a + $b,
            zero: 1,
            fa: $F->pure(41),
        );
    }

    public function option(OptionInstance $optionI): int
    {
        return $this->fold($optionI);
    }

    /**
     * @param EitherInstance<Err> $eitherI
     */
    public function either(EitherInstance $eitherI): int
    {
        return $this->fold($eitherI);
    }

    public function lst(LstInstance $lstI): int
    {
        return $this->fold($lstI);
    }
}
