<?php

namespace Kfriars\TranslationsManager\Commands;

use Illuminate\Console\Command;

use Kfriars\TranslationsManager\Contracts\LocaleContract;
use Kfriars\TranslationsManager\Contracts\ManagerContract;
use Kfriars\TranslationsManager\Contracts\TranslationsFileContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;

class TranslationsStatusCommand extends Command
{
    public $signature = 'translations:status
                        {locales?* : The locales to undergo validation. (If empty, all locales will validate)}';

    public $description = 'List the status of all translations in all languages.';

    /**
     * Execute the command
     *
     * @param ManagerContract $manager
     * @return int
     * @throws InvalidArgumentException
     */
    public function handle(ManagerContract $manager)
    {
        $locales = $this->argument('locales');
        
        try {
            $listing = $manager->listing($locales);
        } catch (TranslationsManagerException $e) {
            $this->error($e->getMessage());
            
            return 1;
        }

        foreach ($listing->locales()->all() as $locale) {
            $table = new Table($this->output);

            $table->setHeaders([
                [new TableCell('Locale: '.$locale->code(), ['colspan' => 4])],
            ]);
            $table->setRows($this->makeRows($locale));
            $table->render();
            $this->line('');
            $this->line('');
        }

        return 0;
    }

    /**
     * Convert the status of a Locale to an array of table rows
     *
     * @param LocaleContract $locale
     * @return array
     */
    protected function makeRows(LocaleContract $locale): array
    {
        $rows = [
            ['File', new TableCell('Status', ['colspan' => 2]), 'Ignored'],
            new TableSeparator(),
        ];

        foreach ($locale->files()->all() as $file) {
            foreach ($this->fileToRows($file) as $row) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Convert a translation files status to table rows
     *
     * @param TranslationsFileContract $file
     * @return array
     */
    protected function fileToRows(TranslationsFileContract $file): array
    {
        $numErrors = $file->errors(false)->count();

        if (! $numErrors) {
            return [[
                $file->path(),
                new TableCell('✓', ['colspan' => 2]),
                $file->ignored() ? '✓' : '',
            ]];
        }

        $rows = [];

        foreach ($file->errors(false)->all() as $idx => $error) {
            $rows[] = [
                $idx === 0 ? $file->path() : '',
                $error->key(),
                $error->message(),
                $file->ignored() || $error->ignored() ? '✓' : '',
            ];
        }

        return $rows;
    }
}
