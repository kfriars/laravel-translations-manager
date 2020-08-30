<?php

namespace Kfriars\TranslationsManager\Contracts;

interface TranslationsFilesContract
{
    /**
     * List all translations files for a given locale
     *
     * @param string $locale
     * @param string|null $subfolder
     * @return array
     */
    public function listLocale(string $locale, ?string $subfolder = null): array;
}
