<?php

namespace Kfriars\TranslationsManager\Commands;

use Illuminate\Console\Command;
use Kfriars\TranslationsManager\Contracts\ManagerContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;

class TranslationsErrorsCommand extends Command
{
    public $signature = 'translations:errors
                        {locales?* : The locales to undergo validation. (If empty, all locales will validate)}
                        {--no-ignore : Ignored errors will be listed}';

    public $description = 'List errors in translations files.';

    public function handle(ManagerContract $manager)
    {
        $locales = $this->argument('locales');
        $ignore = ! $this->option('no-ignore');
        
        try {
            $errors = $manager->errors($locales, $ignore);
        } catch (TranslationsManagerException $e) {
            $this->error($e->getMessage());
            
            return 1;
        }

        $numErrors = $errors->count();

        if (! $numErrors) {
            $this->line('There are no errors in the translations files!');

            return 0;
        }

        $this->line("There are {$numErrors} error(s) in the translations files:");
        $this->line('');

        $separator = new TableSeparator();
        $grouped = $errors->groupBy(['locale', 'file']);

        foreach ($grouped as $locale => $files) {
            foreach ($files as $file => $errors) {
                $table = new Table($this->output);

                $table->setHeaders([
                    [new TableCell($locale.'/'.$file, ['colspan' => 2])],
                ]);

                $rows = [
                    ['Key', 'Message'],
                    $separator,
                ];

                foreach ($errors as $error) {
                    $rows[] = [
                        'key' => $error->key(),
                        'message' => $error->message(),
                    ];
                }

                $table->setRows($rows);
                $table->render();
                $this->line('');
                $this->line('');
            }
        }

        return 1;
    }
}
