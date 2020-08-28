<?php

namespace Kfriars\TranslationsManager\Contracts;

interface ValidatorContract
{
    /**
     * Compare all supported lang files in the listing against reference language's lang files
     *
     * @param string $referenceLocale
     * @param array $files
     * @param array $locales
     *
     * @return ListingContract
     */
    public function validate(string $referenceLocale, array $files, array $locales): ListingContract;
}
