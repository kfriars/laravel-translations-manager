<?php

namespace Kfriars\TranslationsManager;

use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Contracts\ListingContract;
use Kfriars\TranslationsManager\Contracts\ManagerContract;
use Kfriars\TranslationsManager\Contracts\TranslationsFilesContract;
use Kfriars\TranslationsManager\Contracts\ValidatorContract;
use Kfriars\TranslationsManager\Entities\ErrorCollection;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsManager implements ManagerContract
{
    /** @var TranslationsFilesContract */
    protected $files;

    /** @var ValidatorContract */
    protected $validator;

    /** @var string */
    protected $referenceLocale = '';
    
    /** @var string[] */
    protected $availableLocales = [];

    /** @var string[] */
    protected $supportedLocales = [];

    public function __construct(
        TranslationsFilesContract $files,
        ConfigContract $config,
        ValidatorContract $validator
    ) {
        $this->files = $files;
        $this->validator = $validator;

        $this->availableLocales = $config->availableLocales();
        $this->referenceLocale = $config->referenceLocale();
        $this->supportedLocales = $config->supportedLocales();
    }

    /**
     * Get the status of every translation, in every file relative to the reference language
     *
     * @param null|array $locales
     * @return ListingContract
     * @throws TranslationsManagerException
     */
    public function listing(?array $locales = null): ListingContract
    {
        $locales = $this->locales($locales);

        $files = $this->files->listLocale($this->referenceLocale);

        return $this->validator->validate($this->referenceLocale, $files, $locales);
    }
    
    /**
     * List all errors in all of the given locales
     *
     * @param null|array $locales
     * @param bool $useIgnores If null all supported locales will be checked
     * @return ErrorCollection
     * @throws TranslationsManagerException
     */
    public function errors(?array $locales = null, bool $useIgnores = true): ErrorCollection
    {
        $locales = $this->locales($locales);

        $files = $this->files->listLocale($this->referenceLocale);
        $listing = $this->validator->validate($this->referenceLocale, $files, $locales);

        return $listing->errors($useIgnores);
    }

    /**
     * Check if there are any errors in any of the given locales
     *
     * @param null|array $locales If null all supported locales will be checked
     * @param bool $useIgnores
     * @return bool
     * @throws TranslationsManagerException
     */
    public function hasErrors(?array $locales = null, bool $useIgnores = true): bool
    {
        $locales = $this->locales($locales);

        $files = $this->files->listLocale($this->referenceLocale);
        $listing = $this->validator->validate($this->referenceLocale, $files, $locales);

        return $listing->hasErrors($useIgnores);
    }
    
    /**
     * Validate the locales given, if none are given, return all supported languages
     *
     * @param null|array $locales
     * @return array
     * @throws TranslationsManagerException
     */
    protected function locales(?array $locales): array
    {
        if ($locales === null || empty($locales)) {
            return $this->supportedLocales;
        }

        if (array_search($this->referenceLocale, $locales) !== false) {
            throw new TranslationsManagerException(
                "You can not use the translations manager with the reference locale '{$this->referenceLocale}'"
            );
        }
        
        if (count(($missing = array_diff($locales, $this->availableLocales)))) {
            throw new TranslationsManagerException(
                "You do not have a locale folder for the given locale(s) '".implode(', ', $missing)."'."
            );
        }

        return $locales;
    }
}
