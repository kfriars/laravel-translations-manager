<?php

namespace Kfriars\TranslationsManager\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\Translator;
use Kfriars\ArrayToFile\Contracts\FileContract;
use Kfriars\ArrayToFile\File;
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
use Kfriars\TranslationsManager\TranslationsConfig;
use Kfriars\TranslationsManager\TranslationsFiles;
use Kfriars\TranslationsManager\TranslationsArrayFile;
use Kfriars\TranslationsManager\TranslationsFixer;
use Kfriars\TranslationsManager\TranslationsFixesJSONFormatter;
use Kfriars\TranslationsManager\TranslationsFixesValidator;
use Kfriars\TranslationsManager\TranslationsIgnores;
use Kfriars\TranslationsManager\TranslationsLockfiles;
use Kfriars\TranslationsManager\TranslationsManager;
use Kfriars\TranslationsManager\TranslationsValidator;

class DeferredServicesProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
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
        ];
    }

    public function register(): void
    {
        $this->bindArrayFileContract()
             ->bindTranslationsFilesContract()
             ->bindConfigContract()
             ->bindFormatterContract()
             ->bindLockfilesContract()
             ->bindIgnoresContract()
             ->bindValidatorContract()
             ->bindFixerContract()
             ->bindManagerContract();
    }

    public function bindArrayFileContract(): self
    {
        $this->app->bind(FileContract::class, File::class);

        $this->app->bind(ArrayFileContract::class, function (Application $app) {
            return $app[TranslationsArrayFile::class];
        });

        return $this;
    }

    public function bindTranslationsFilesContract(): self
    {
        $this->app->bind(TranslationsFilesContract::class, function (Application $app) {
            return new TranslationsFiles(
                $app[ConfigContract::class],
                $app[FilesystemManager::class]
            );
        });

        return $this;
    }

    public function bindConfigContract(): self
    {
        $this->app->bind(ConfigContract::class, function (Application $app) {
            return new TranslationsConfig($app[FilesystemManager::class]);
        });

        return $this;
    }

    public function bindFormatterContract(): self
    {
        $this->app->bind(FormatterContract::class, function (Application $app) {
            return $this->app->make(config('translations-manager.formatter'), [
                $app[ConfigContract::class]
            ]);
        });

        return $this;
    }

    public function bindLockfilesContract(): self
    {
        $this->app->bind(LockfilesContract::class, function (Application $app) {
            return new TranslationsLockfiles(
                $app[ConfigContract::class],
                $app[ArrayFileContract::class]
            );
        });

        return $this;
    }

    public function bindIgnoresContract(): self
    {
        $this->app->bind(IgnoresContract::class, function (Application $app) {
            return new TranslationsIgnores(
                $app[ConfigContract::class],
                $app[ArrayFileContract::class]
            );
        });

        return $this;
    }

    public function bindValidatorContract(): self
    {
        $this->app->bind(ValidatorContract::class, function (Application $app) {
            return new TranslationsValidator(
                $app[LockfilesContract::class],
                $app[IgnoresContract::class],
                $app['translator']
            );
        });

        return $this;
    }

    public function bindFixerContract(): self
    {
        $this->app->bind(FixesValidatorContract::class, function (Application $app) {
            return new TranslationsFixesValidator(
                $app[ConfigContract::class],
                $app[FormatterContract::class],
                $app[Translator::class]
            );
        });
        
        $this->app->bind(FixerContract::class, function (Application $app) {
            return new TranslationsFixer(
                $app[ConfigContract::class],
                $app[FormatterContract::class],
                $app[ArrayFileContract::class],
                $app[FixesValidatorContract::class],
                $app[LockfilesContract::class],
                $app[Translator::class]
            );
        });

        return $this;
    }

    public function bindManagerContract(): self
    {
        $this->app->bind(ManagerContract::class, function (Application $app) {
            return new TranslationsManager(
                $app[TranslationsFilesContract::class],
                $app[ConfigContract::class],
                $app[ValidatorContract::class]
            );
        });

        return $this;
    }
}
