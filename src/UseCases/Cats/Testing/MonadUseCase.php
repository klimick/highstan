<?php

declare(strict_types=1);

namespace Highstan\UseCases\Cats\Testing;

use Highstan\HKEncoding\HK;
use Highstan\UseCases\Cats\Either\EitherInstance;
use Highstan\UseCases\Cats\Either\EitherTypeLambda;
use Highstan\UseCases\Cats\Lst\LstInstance;
use Highstan\UseCases\Cats\Lst\LstTypeLambda;
use Highstan\UseCases\Cats\Option\OptionInstance;
use Highstan\UseCases\Cats\Option\OptionTypeLambda;

final readonly class MonadUseCase
{
    /**
     * @return HK<OptionTypeLambda, int>
     */
    public function option(OptionInstance $optionI): HK
    {
        return $optionI->flatMap(
            $optionI->pure(1),
            static fn(int $value) => $optionI->pure($value + 41),
        );
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @return HK<EitherTypeLambda<Err>, int>
     */
    public function either(EitherInstance $eitherI): HK
    {
        return $eitherI->flatMap(
            $eitherI->pure(1),
            static fn(int $value) => $eitherI->pure($value + 41),
        );
    }

    /**
     * @return HK<LstTypeLambda, int>
     */
    public function lst(LstInstance $lstI): HK
    {
        return $lstI->flatMap(
            $lstI->pure(1),
            static fn(int $value) => $lstI->pure($value + 41),
        );
    }
}
