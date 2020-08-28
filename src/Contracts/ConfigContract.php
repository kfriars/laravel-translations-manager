<?php

namespace Kfriars\TranslationsManager\Contracts;

use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

interface ConfigContract
{
    /**
     * Get all available locales in the system
     *
     * @return string[]
     */
    public function availableLocales(): array;
    
    /**
     * Validate and retirve the reference language
     *
     * @return string
     * @throws TranslationsManagerException
     */
    public function referenceLocale(): string;

    /**
     * Validate and retrieve the supported locales
     *
     * @return string[]
     * @throws TranslationsManagerException
     */
    public function supportedLocales(): array;
}
