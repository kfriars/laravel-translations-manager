<?php

namespace Kfriars\TranslationsManager\Commands;

use Illuminate\Console\Command;
use Kfriars\TranslationsManager\Contracts\FixerContract;
use Kfriars\TranslationsManager\Contracts\ManagerContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsCleanCommand extends Command
{
    public $signature = 'translations:clean
                        {locales?* : The locales to clean. (If empty, all locales will be cleaned)}';

    public $description = 'Clean all dead translations files in the specified locales.';

    public function handle(ManagerContract $manager, FixerContract $fixer)
    {
        $locales = $this->argument('locales');
        
        try {
            $listing = $manager->listing($locales);
            $cleaned = $fixer->clean($listing);
        } catch (TranslationsManagerException $e) {
            $this->error($e->getMessage());
            
            return 1;
        }

        $this->line("There were {$cleaned} error(s) dead translations cleaned from the supported locales");

        return 0;
    }
}
