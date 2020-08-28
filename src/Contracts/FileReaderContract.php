<?php

namespace Kfriars\TranslationsManager\Contracts;

interface FileReaderContract
{
    /**
     * Get the names of all of the locales defined in the lang folder directory
     *
     * @return array
     */
    public function localeFolders(): array;

    /**
     * List all translations files for a given locale
     *
     * @param string $locale
     * @param string|null $subfolder
     * @return array
     */
    public function listLocale(string $locale, ?string $subfolder = null): array;
}
