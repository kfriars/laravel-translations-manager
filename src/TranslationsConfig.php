<?php

namespace Kfriars\TranslationsManager;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsConfig implements ConfigContract
{
    /** @var FilesystemManager */
    protected $manager;

    /** @var string */
    protected $referenceLocale = '';
    
    /** @var string[] */
    protected $availableLocales = [];

    /** @var string[] */
    protected $supportedLocales = [];

    public function __construct(
        FilesystemManager $manager
    ) {
        $this->manager = $manager;
        
        $this->availableLocales = $this->initAvailableLocales();
        $this->referenceLocale = $this->initReferenceLocale();
        $this->supportedLocales = $this->initSupportedLocales();
    }

    /**
     * Get a filesystem with its root set to the directory of the lang folder (resources/lang)
     *
     * @return Filesystem
     */
    public function lang(): Filesystem
    {
        return $this->manager->createLocalDriver([ 'root' => $this->langDir() ]);
    }

    /**
     * The directory of the lang folder (resources/lang)
     *
     * @return string
     */
    public function langDir(): string
    {
        $dir = config('translations-manager.lang_dir');

        if ($dir === null) {
            throw new TranslationsManagerException("You do not have Laravel's 'lang' directory set in the translations-manager config file.");
        }

        return (string) $dir;
    }

    /**
     * The directory the rest of the packages files are stored in (storage/translations)
     *
     * @return string
     */
    public function storageDir(): string
    {
        $dir = config('translations-manager.storage_dir');

        if ($dir === null) {
            throw new TranslationsManagerException("You do not have 'storage_dir' configured for the translations-manager.");
        }

        return (string) $dir;
    }

    /**
     * Get a filesystem with its root set to the directory of the storage folder (storage/translations)
     *
     * @return Filesystem
     */
    public function storage(): Filesystem
    {
        return $this->manager->createLocalDriver(['root' => $this->storageDir()]);
    }

    /**
     * Get a filesystem with its root set to the directory of the lock folder (storage/translations/lock)
     *
     * @return Filesystem
     */
    public function lock(): Filesystem
    {
        return $this->manager->createLocalDriver([ 'root' => $this->lockDir() ]);
    }

    /**
     * Get the lock directory (storage/translations/lock)
     *
     * @return string
     */
    public function lockDir(): string
    {
        return $this->storageDir().DIRECTORY_SEPARATOR.'lock';
    }

    /**
     * Get the filepath to the ignores file (storage/translations/ignores.php)
     *
     * @return string
     */
    public function ignoresPath(): string
    {
        return $this->storageDir().DIRECTORY_SEPARATOR.'ignores.php';
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
    public function initAvailableLocales(): array
    {
        return $this->lang()->directories();
    }

    /**
     * Get a filesystem with its root set to the directory of the fixes folder (storage/translations/fixes)
     *
     * @return Filesystem
     */
    public function fixes(): Filesystem
    {
        return $this->manager->createLocalDriver([ 'root' => $this->fixesDir() ]);
    }
    
    /**
     * Get the fixes directory (storage/translations/fixes)
     *
     * @return string
     */
    public function fixesDir(): string
    {
        return $this->storageDir().DIRECTORY_SEPARATOR.'fixes';
    }

    /**
     * Get a filesystem with its root set to the directory of the fixed folder (storage/translations/fixed)
     *
     * @return Filesystem
     */
    public function fixed(): Filesystem
    {
        return $this->manager->createLocalDriver([ 'root' => $this->fixedDir() ]);
    }

    /**
     * Get the fixed directory (storage/translations/fixed)
     *
     * @return string
     */
    public function fixedDir(): string
    {
        return $this->storageDir().DIRECTORY_SEPARATOR.'fixed';
    }

    /**
     * Get the naming convention for fix files
     *
     * @return string
     */
    public function fixNameFormat(): string
    {
        $format = config('translations-manager.fix_name_format');

        if ($format === null) {
            throw new TranslationsManagerException("You do not have the 'fix_name_format' set in the translations-manager config file.");
        }

        return (string) $format;
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
        $configured = (array) config('translations-manager.supported_locales');

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
