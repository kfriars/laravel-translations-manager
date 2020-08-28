<?php

namespace Kfriars\TranslationsManager;

use Illuminate\Support\ServiceProvider;
use Kfriars\TranslationsManager\Commands\TranslationsCleanCommand;
use Kfriars\TranslationsManager\Commands\TranslationsErrorsCommand;
use Kfriars\TranslationsManager\Commands\TranslationsFixCommand;
use Kfriars\TranslationsManager\Commands\TranslationsGenerateFixesCommand;
use Kfriars\TranslationsManager\Commands\TranslationsIgnoreCommand;
use Kfriars\TranslationsManager\Commands\TranslationsStatusCommand;
use Kfriars\TranslationsManager\Commands\TranslationsUnignoreCommand;
use Kfriars\TranslationsManager\Commands\TranslationsValidateCommand;

class TranslationsManagerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/translations-manager.php' => config_path('translations-manager.php'),
            ], 'config');

            $this->commands([
                TranslationsValidateCommand::class,
                TranslationsErrorsCommand::class,
                TranslationsStatusCommand::class,
                TranslationsIgnoreCommand::class,
                TranslationsUnignoreCommand::class,
                TranslationsGenerateFixesCommand::class,
                TranslationsFixCommand::class,
                TranslationsCleanCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'translations-manager');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/translations-manager.php', 'translations-manager');
    }
}
