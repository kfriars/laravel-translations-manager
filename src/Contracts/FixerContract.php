<?php

namespace Kfriars\TranslationsManager\Contracts;

use Illuminate\Contracts\Container\BindingResolutionException;
use Kfriars\ArrayToFile\Exceptions\FileSaveException;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

interface FixerContract
{
    /**
     * Write fix files for all locales in the listing in json format
     *
     * @param ListingContract $listing
     * @return void
     * @throws BindingResolutionException
     */
    public function generateFixFiles(ListingContract $listing): void;

    /**
     * Fix a locale's translations using it's fix file
     *
     * @param string $locale
     * @return void
     * @throws TranslationsManagerException
     * @throws FileSaveException
     */
    public function fix(string $locale): void;
    
    /**
     * Fix the specified locale's translations using the fix files
     *
     * @param array $locales
     * @return void
     * @throws TranslationsManagerException
     * @throws FileSaveException
     */
    public function fixMany(array $locales): void;

    /**
     * Clean dead translations from the listing (no_reference_translation)
     *
     * @param ListingContract $listing
     * @return int
     */
    public function clean(ListingContract $listing): int;
}
