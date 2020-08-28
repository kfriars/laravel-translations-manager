<?php

namespace Kfriars\TranslationsManager\Entities;

use Kfriars\TranslationsManager\Contracts\ListingContract;
use Kfriars\TranslationsManager\Contracts\LocaleContract;

class Listing implements ListingContract
{
    /** @var string */
    protected $referenceLocale;

    /** @var LocaleCollection */
    protected $locales;
    
    public function __construct(string $referenceLocale)
    {
        $this->referenceLocale = $referenceLocale;
        $this->locales = new LocaleCollection();
    }
    
    /**
     * Get the reference locale the listing was made with
     *
     * @return string
     */
    public function referenceLocale(): string
    {
        return $this->referenceLocale;
    }

    /**
     * Get all locales in the Listing
     *
     * @return LocaleCollection
     */
    public function locales(): LocaleCollection
    {
        return $this->locales;
    }

    /**
     * Add a locale to the listing
     *
     * @param string $code
     * @return void
     */
    public function addLocale(LocaleContract $locale): void
    {
        $this->locales->push($locale);
    }

    /**
     * List all errors from all locales in the listing, if ignore is set, only list errors that are not ignored
     *
     * @param bool $ignore
     * @return ErrorCollection
     */
    public function errors(bool $ignore = true): ErrorCollection
    {
        $errors = new ErrorCollection();

        foreach ($this->locales as $locale) {
            foreach ($locale->errors($ignore) as $error) {
                $errors->push($error);
            }
        }
        
        return $errors;
    }

    /**
     * List all critical errors from all locales in the listing, if ignore is set, only list errors that are not ignored
     *
     * @param bool $ignore
     * @return ErrorCollection
     */
    public function critical(bool $ignore = true): ErrorCollection
    {
        $errors = new ErrorCollection();

        foreach ($this->locales as $locale) {
            foreach ($locale->critical($ignore) as $error) {
                $errors->push($error);
            }
        }
        
        return $errors;
    }

    /**
     * Determine if the listing has any errors, if ignore is set, errors can be ignored
     *
     * @param bool $ignore
     * @return bool
     */
    public function hasErrors(bool $ignore = true): bool
    {
        foreach ($this->locales as $locale) {
            if ($locale->hasErrors($ignore)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get an array representation of the Listing
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'referenceLocale' => $this->referenceLocale,
            'locales' => $this->locales->values()->toArray(),
        ];
    }
}
