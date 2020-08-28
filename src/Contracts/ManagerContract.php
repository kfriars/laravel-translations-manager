<?php

namespace Kfriars\TranslationsManager\Contracts;

use Kfriars\TranslationsManager\Entities\ErrorCollection;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

interface ManagerContract
{
    /**
     * Get the status of every translation, in every file relative to the reference language
     *
     * @param null|array $locales
     * @return ListingContract
     * @throws TranslationsManagerException
     */
    public function listing(?array $locales = null): ListingContract;

    /**
     * List all errors in all of the given locales
     *
     * @param null|array $locales
     * @param bool $useIgnores If null all supported locales will be checked
     * @return ErrorCollection
     * @throws TranslationsManagerException
     */
    public function errors(?array $locales = null, bool $useIgnores = true): ErrorCollection;

    /**
     * Check if there are any errors in any of the given locales
     *
     * @param null|array $locales If null all supported locales will be checked
     * @param bool $useIgnores
     * @return bool
     * @throws TranslationsManagerException
     */
    public function hasErrors(?array $locales = null, bool $useIgnores = true): bool;
}
