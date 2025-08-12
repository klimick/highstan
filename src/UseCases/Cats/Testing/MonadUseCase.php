<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Testing;

use Highstan\UseCases\Cats\Either\Either;
use Highstan\UseCases\Cats\Either\EitherInstance;
use Highstan\UseCases\Cats\Lst\Lst;
use Highstan\UseCases\Cats\Lst\LstInstance;
use Highstan\UseCases\Cats\Option\Option;
use Highstan\UseCases\Cats\Option\OptionInstance;

final readonly class MonadUseCase
{
    /**
     * @return Option<int>
     */
    public function option(OptionInstance $optionI): Option
    {
        return $optionI->flatMap(
            $optionI->pure(1),
            static fn(int $value) => $optionI->pure($value + 41),
        );
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @return Either<Err, int>
     */
    public function either(EitherInstance $eitherI): Either
    {
        return $eitherI->flatMap(
            $eitherI->pure(1),
            static fn(int $value) => $eitherI->pure($value + 41),
        );
    }

    /**
     * @return Lst<int>
     */
    public function lst(LstInstance $lstI): Lst
    {
        return $lstI->flatMap(
            $lstI->pure(1),
            static fn(int $value) => $lstI->pure($value + 41),
        );
    }
}
