<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Testing;

use Highstan\UseCases\Cats\Either\Either;
use Highstan\UseCases\Cats\Either\EitherInstance;
use Highstan\UseCases\Cats\Lst\Lst;
use Highstan\UseCases\Cats\Lst\LstInstance;
use Highstan\UseCases\Cats\Option\Option;
use Highstan\UseCases\Cats\Option\OptionInstance;

final readonly class ApplicativeUseCase
{
    /**
     * @return Option<int>
     */
    public function option(OptionInstance $optionI): Option
    {
        return $optionI->pure(42);
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @return Either<Err, int>
     */
    public function either(EitherInstance $eitherI): Either
    {
        return $eitherI->pure(42);
    }

    /**
     * @return Lst<int>
     */
    public function lst(LstInstance $lstInstance): Lst
    {
        return $lstInstance->pure(42);
    }
}
