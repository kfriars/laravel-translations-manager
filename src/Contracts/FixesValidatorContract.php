<?php

namespace Kfriars\TranslationsManager\Contracts;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

interface FixesValidatorContract
{
    /**
     * Ensure there are no critical errors that should cause aborting to fail
     *
     * @param ListingContract $listing
     * @return void
     * @throws TranslationsManagerException
     */
    public function validateGenerate(ListingContract $listing): void;

    /**
     * Validate that the fix files are valid and they agree with the state of the
     * reference locale
     *
     * @param array $locales
     * @return array
     * @throws TranslationsManagerException
     * @throws FileNotFoundException
     */
    public function validateFixes(array $locales): array;
}
