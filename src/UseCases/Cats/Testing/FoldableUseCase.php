<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Testing;

use Highstan\UseCases\Cats\Either\EitherInstance;
use Highstan\UseCases\Cats\Lst\LstInstance;
use Highstan\UseCases\Cats\Option\OptionInstance;
use Highstan\UseCases\Cats\TypeClass\Applicative;
use Highstan\UseCases\Cats\TypeClass\Foldable;

final readonly class FoldableUseCase
{
    /**
     * @template F of type-lam<_>
     * @param Foldable<F> & Applicative<F> $F
     */
    public function fold(Foldable&Applicative $F): int
    {
        return $F->fold(
            fa: $F->pure(41),
            zero: 1,
            reducer: static fn(int $a, int $b) => $a + $b,
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
