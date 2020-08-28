<?php

namespace Kfriars\TranslationsManager\Commands;

use Illuminate\Console\Command;
use Kfriars\TranslationsManager\Contracts\FixerContract;
use Kfriars\TranslationsManager\Contracts\ManagerContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsGenerateFixesCommand extends Command
{
    public $signature = 'translations:generate-fixes
                        {locales?* : The locales to generate fix files for. (If empty, all will be generated)}';

    public $description = 'Generate the files that can fix the errors in locales';

    public function handle(ManagerContract $manager, FixerContract $fixer)
    {
        $locales = $this->argument('locales');
        
        try {
            $listing = $manager->listing($locales);
            $fixer->generateFixFiles($listing);
        } catch (TranslationsManagerException $e) {
            $this->error($e->getMessage());
            
            return 1;
        }

        $listed = implode("', '", $locales);

        $this->info("Fix files have been generated for '{$listed}'.");

        return 0;
    }
}
