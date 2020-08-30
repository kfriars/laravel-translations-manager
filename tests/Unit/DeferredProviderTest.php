<?php

namespace Kfriars\TranslationsManager\Tests\Unit;

use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Contracts\TranslationsFilesContract;
use Kfriars\TranslationsManager\Contracts\ArrayFileContract;
use Kfriars\TranslationsManager\Contracts\FixerContract;
use Kfriars\TranslationsManager\Contracts\FixesValidatorContract;
use Kfriars\TranslationsManager\Contracts\FormatterContract;
use Kfriars\TranslationsManager\Contracts\IgnoresContract;
use Kfriars\TranslationsManager\Contracts\LockfilesContract;
use Kfriars\TranslationsManager\Contracts\ManagerContract;
use Kfriars\TranslationsManager\Contracts\ValidatorContract;
use Kfriars\TranslationsManager\Providers\DeferredServicesProvider;
use Kfriars\TranslationsManager\Tests\TestCase;

class DefereredProviderTest extends TestCase
{
    /** @test */
    public function it_provides_implementations()
    {
        $provider = new DeferredServicesProvider($this->app);

        $this->assertEquals([
            ArrayFileContract::class,
            TranslationsFilesContract::class,
            ConfigContract::class,
            FormatterContract::class,
            LockfilesContract::class,
            IgnoresContract::class,
            ValidatorContract::class,
            FixesValidatorContract::class,
            FixerContract::class,
            ManagerContract::class,
        ], $provider->provides());
    }
}
