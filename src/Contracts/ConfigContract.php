<?php

namespace Kfriars\TranslationsManager\Contracts;

use Illuminate\Contracts\Filesystem\Filesystem;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

interface ConfigContract
{
    /**
     * Get a filesystem with its root set to the directory of the lang folder (resources/lang)
     *
     * @return Filesystem
     */
    public function lang(): Filesystem;
    
    /**
     * The directory of the lang folder (resources/lang)
     *
     * @return string
     */
    public function langDir(): string;
    
    /**
     * The directory the rest of the packages files are stored in (storage/translations)
     *
     * @return string
     */
    public function storageDir(): string;
    
    /**
     * Get a filesystem with its root set to the directory of the storage folder (storage/translations)
     *
     * @return Filesystem
     */
    public function storage(): Filesystem;
    
    /**
     * Get a filesystem with its root set to the directory of the lock folder (storage/translations/lock)
     *
     * @return Filesystem
     */
    public function lock(): Filesystem;

    /**
     * Get the lock directory (storage/translations/lock)
     *
     * @return string
     */
    public function lockDir(): string;
    
    /**
     * Get the filepath to the ignores file (storage/translations/ignores.php)
     *
     * @return string
     */
    public function ignoresPath(): string;
    
    /**
     * Get all locales availabe in the system
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
    
    /**
     * Get a filesystem with its root set to the directory of the lock folder (storage/translations/fixes)
     *
     * @return Filesystem
     */
    public function fixes(): Filesystem;

    /**
     * Get the fixed directory (storage/translations/fixed)
     *
     * @return string
     */
    public function fixedDir(): string;
    
    /**
     * Get a filesystem with its root set to the directory of the lock folder (storage/translations/fixed)
     *
     * @return Filesystem
     */
    public function fixed(): Filesystem;

    /**
     * Get the fixes directory (storage/translations/fixes)
     *
     * @return string
     */
    public function fixesDir(): string;
    
    /**
     * Get the naming convention for fix files
     *
     * @return string
     */
    public function fixNameFormat(): string;
}
