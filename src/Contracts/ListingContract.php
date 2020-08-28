<?php

namespace Kfriars\TranslationsManager\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Kfriars\TranslationsManager\Entities\ErrorCollection;
use Kfriars\TranslationsManager\Entities\LocaleCollection;

interface ListingContract extends Arrayable
{
    /**
     * Get the reference locale the listing was made with
     *
     * @return string
     */
    public function referenceLocale(): string;
    
    /**
     * Get all locales in the Listing
     *
     * @return LocaleCollection
     */
    public function locales(): LocaleCollection;
    
    /**
     * Add a locale to the listing
     *
     * @param string $code
     * @return void
     */
    public function addLocale(LocaleContract $locale): void;
    
    /**
     * List all errors from all locales in the listing, if ignore is set, only list errors that are not ignored
     *
     * @param bool $ignore
     * @return ErrorCollection
     */
    public function errors(bool $ignore = true): ErrorCollection;

    /**
     * List all critical errors from all locales in the listing, if ignore is set, only list errors that are not ignored
     *
     * @param bool $ignore
     * @return ErrorCollection
     */
    public function critical(bool $ignore = true): ErrorCollection;
    
    /**
     * Determine if the listing has any errors, if ignore is set, errors can be ignored
     *
     * @param bool $ignore
     * @return bool
     */
    public function hasErrors(bool $ignore = true): bool;
}
