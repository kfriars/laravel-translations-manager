<?php

namespace Kfriars\TranslationsManager\Commands;

use Illuminate\Console\Command;
use Kfriars\TranslationsManager\Contracts\FixerContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsFixCommand extends Command
{
    public $signature = 'translations:fix
                        {locales* : The locales to fix. (If empty, all will be generated)}';

    public $description = 'Generate the files that can fix the errors in locales';

    public function handle(FixerContract $fixer)
    {
        $locales = $this->argument('locales');
        
        try {
            $fixer->fixMany($locales);
        } catch (TranslationsManagerException $e) {
            $this->error($e->getMessage());
            
            return 1;
        }

        $listed = implode("', '", $locales);

        $this->info("The locale(s) '{$listed}' have been fixed.");

        return 0;
    }
}
