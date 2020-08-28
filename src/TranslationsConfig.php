<?php

namespace Kfriars\TranslationsManager;

use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Contracts\FileReaderContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsConfig implements ConfigContract
{
    /** @var FileReaderContract */
    protected $files;

    /** @var string */
    protected $referenceLocale = '';
    
    /** @var string[] */
    protected $availableLocales = [];

    /** @var string[] */
    protected $supportedLocales = [];

    public function __construct(
        FileReaderContract $files
    ) {
        $this->files = $files;

        $this->availableLocales = $this->initAvailableLocales();
        $this->referenceLocale = $this->initReferenceLocale();
        $this->supportedLocales = $this->initSupportedLocales();
    }
    
    /**
     * Get all locales availabe in the system
     *
     * @return string[]
     */
    public function availableLocales(): array
    {
        return $this->availableLocales;
    }

    /**
     * Get the reference locale for the system
     *
     * @return string
     */
    public function referenceLocale(): string
    {
        return $this->referenceLocale;
    }

    /**
     * Get the supported locales of the system
     *
     * @return string[]
     */
    public function supportedLocales(): array
    {
        return $this->supportedLocales;
    }

    /**
     * Get all available locales in the system
     *
     * @return string[]
     */
    protected function initAvailableLocales(): array
    {
        return $this->files->localeFolders();
    }

    /**
     * Validate and retrieve the reference language
     *
     * @return string
     * @throws TranslationsManagerException
     */
    protected function initReferenceLocale(): string
    {
        $reference = (string) config('translations-manager.reference_locale');

        if (! $reference) {
            throw new TranslationsManagerException(
                "You must set the reference_locale in your translations-manager config file."
            );
        }

        if (array_search($reference, $this->availableLocales) === false) {
            throw new TranslationsManagerException(
                "You do not have a folder for the reference locale '{$reference}'"
            );
        }

        return $reference;
    }

    /**
     * Validate and retrieve the supported locales
     *
     * @return string[]
     * @throws TranslationsManagerException
     */
    protected function initSupportedLocales(): array
    {
        $configured = config('translations-manager.supported_locales');

        if ($configured) {
            if (count(($missing = array_diff($configured, $this->availableLocales)))) {
                throw new TranslationsManagerException(
                    "You do not have a locale folder for the supported locales '".
                    implode(', ', $missing)."' in your config file"
                );
            }

            return array_filter($configured, function ($locale) {
                return $locale !== $this->referenceLocale;
            });
        }

        return array_filter($this->availableLocales, function ($locale) {
            return $locale !== $this->referenceLocale;
        });
    }
}
