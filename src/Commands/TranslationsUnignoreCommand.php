<?php

namespace Kfriars\TranslationsManager\Commands;

use Illuminate\Console\Command;
use Kfriars\TranslationsManager\Contracts\IgnoresContract;

class TranslationsUnignoreCommand extends Command
{
    public $signature = 'translations:unignore
                        {locale : The locale of the error to be ignored}
                        {file : The file of the error to be ignored}
                        {key? : The key of the error to be ignored}';

    public $description = 'Unignore a translations file error';

    public function handle(IgnoresContract $ignores)
    {
        $locale = $this->argument('locale');
        $file = $this->argument('file');
        $key = $this->argument('key');

        $ignores->unignore($locale, $file, $key);

        $this->info("Successfully unignored.");

        return 0;
    }
}
