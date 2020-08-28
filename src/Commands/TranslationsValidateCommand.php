<?php

namespace Kfriars\TranslationsManager\Commands;

use Illuminate\Console\Command;
use Kfriars\TranslationsManager\Contracts\ManagerContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsValidateCommand extends Command
{
    public $signature = 'translations:validate
                        {locales?* : The locales to undergo validation. (If empty, all locales will validate)}
                        {--no-ignore : Ignored errors will not cause failure}';

    public $description = 'Determine if there are any errors in translations files.';

    public function handle(ManagerContract $manager)
    {
        $locales = $this->argument('locales');
        $ignore = ! $this->option('no-ignore');

        try {
            $hasErrors = $manager->hasErrors($locales, $ignore);
        } catch (TranslationsManagerException $e) {
            $this->error($e->getMessage());
            
            return 1;
        }

        if ($hasErrors) {
            $this->line('<bg=red;fg=white>Validation Failed</>');

            return 1;
        }

        $this->line('<bg=green;fg=white>Validation Passed</>');

        return 0;
    }
}
