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
use Kfriars\TranslationsManager\Contracts\FileReaderContract;
use Kfriars\TranslationsManager\Contracts\FileWriterContract;
use Kfriars\TranslationsManager\Contracts\FixerContract;
use Kfriars\TranslationsManager\Contracts\FixesValidatorContract;
use Kfriars\TranslationsManager\Contracts\IgnoresContract;
use Kfriars\TranslationsManager\Contracts\LockfilesContract;
use Kfriars\TranslationsManager\Contracts\ManagerContract;
use Kfriars\TranslationsManager\Contracts\ValidatorContract;
use Kfriars\TranslationsManager\TranslationsConfig;
use Kfriars\TranslationsManager\TranslationsFileReader;
use Kfriars\TranslationsManager\TranslationsFileWriter;
use Kfriars\TranslationsManager\TranslationsFixer;
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
            FileWriterContract::class,
            FileReaderContract::class,
            ConfigContract::class,
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
        // $this->ensureDirectoriesExist();

        $this->bindFileWriterContract()
             ->bindFileReaderContract()
             ->bindConfigContract()
             ->bindLockfilesContract()
             ->bindIgnoresContract()
             ->bindValidatorContract()
             ->bindFixerContract()
             ->bindManagerContract();
    }

    public function bindFileWriterContract(): self
    {
        $this->app->bind(FileContract::class, File::class);

        $this->app->bind(FileWriterContract::class, function (Application $app) {
            return $app[TranslationsFileWriter::class];
        });

        return $this;
    }

    public function bindFileReaderContract(): self
    {
        $this->app->bind(FileReaderContract::class, function (Application $app) {
            return new TranslationsFileReader($app[FilesystemManager::class]);
        });

        return $this;
    }

    public function bindConfigContract(): self
    {
        $this->app->bind(ConfigContract::class, function (Application $app) {
            return new TranslationsConfig($app[FileReaderContract::class]);
        });

        return $this;
    }

    public function bindLockfilesContract(): self
    {
        $this->app->bind(LockfilesContract::class, function (Application $app) {
            return new TranslationsLockfiles($app[FileWriterContract::class]);
        });

        return $this;
    }

    public function bindIgnoresContract(): self
    {
        $this->app->bind(IgnoresContract::class, function (Application $app) {
            return new TranslationsIgnores($app[FileWriterContract::class]);
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
                $app[FilesystemManager::class],
                $app[ConfigContract::class],
                $app[Translator::class]
            );
        });
        
        $this->app->bind(FixerContract::class, function (Application $app) {
            return new TranslationsFixer(
                $app[FilesystemManager::class],
                $app[FileWriterContract::class],
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
                $app[FileReaderContract::class],
                $app[ConfigContract::class],
                $app[ValidatorContract::class]
            );
        });

        return $this;
    }
}
