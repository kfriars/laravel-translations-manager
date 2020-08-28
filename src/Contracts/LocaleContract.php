<?php

namespace Kfriars\TranslationsManager\Contracts;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Kfriars\TranslationsManager\Entities\ErrorCollection;
use Kfriars\TranslationsManager\Entities\TranslationsFileCollection;

interface LocaleContract extends Arrayable, ArrayAccess
{
    /**
     * The code of the locale (ie. en, de, fr)
     *
     * @return string
     */
    public function code(): string;
    
    /**
     * Get all files for the listing of the locale
     *
     * @return TranslationsFileCollection
     */
    public function files(): TranslationsFileCollection;
    
    /**
     * Add a lie to the locale
     *
     * @param TranslationsFileContract $file
     * @return void
     */
    public function addFile(TranslationsFileContract $file): void;
    
    /**
     * Get all of the errors in the locale, if ignore is set, only return errors that are not ignored
     *
     * @param bool $ignore
     * @return ErrorCollection
     */
    public function errors(bool $ignore = true): ErrorCollection;

    /**
     * Get all of the critical errors in the locale, if ignore is set, only return errors that are not ignored
     *
     * @param bool $ignore
     * @return ErrorCollection
     */
    public function critical(bool $ignore = true): ErrorCollection;
    
    /**
     * Determine if the locale has any errors, if ignore is set, errors can be ignored
     *
     * @param bool $ignore
     * @return bool
     */
    public function hasErrors(bool $ignore = true): bool;
}
