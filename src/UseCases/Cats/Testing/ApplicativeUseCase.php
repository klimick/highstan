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

final readonly class ApplicativeUseCase
{
    /**
     * @return HK<OptionTypeLambda, int>
     */
    public function option(OptionInstance $optionI): HK
    {
        return $optionI->pure(42);
    }

    /**
     * @param EitherInstance<Err> $eitherI
     * @return HK<EitherTypeLambda<Err>, int>
     */
    public function either(EitherInstance $eitherI): HK
    {
        return $eitherI->pure(42);
    }

    /**
     * @return HK<LstTypeLambda, int>
     */
    public function lst(LstInstance $lstInstance): HK
    {
        return $lstInstance->pure(42);
    }
}
