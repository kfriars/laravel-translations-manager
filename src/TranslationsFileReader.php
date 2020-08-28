<?php

namespace Kfriars\TranslationsManager;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use InvalidArgumentException;
use Kfriars\TranslationsManager\Contracts\FileReaderContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsFileReader implements FileReaderContract
{
    /** @var FilesystemManager */
    protected $manager;

    /** @var string */
    protected $langDirectory;
    
    public function __construct(
        FilesystemManager $manager
    ) {
        $this->manager = $manager;
        $this->langDirectory = config('translations-manager.lang_dir');
    }

    /**
     * Get the names of all of the locales defined in the lang folder directory
     *
     * @return array
     */
    public function localeFolders(): array
    {
        $locales = $this->manager->createLocalDriver(['root' => $this->langDirectory])
                                 ->directories();

        return $locales;
    }

    /**
     * List all files translations for a given locale
     *
     * @param string $locale
     * @param string|null $subfolder
     * @return array
     */
    public function listLocale(string $locale, ?string $subfolder = null): array
    {
        if (DIRECTORY_SEPARATOR === "\\") {
            $subfolder = str_replace("/", DIRECTORY_SEPARATOR, $subfolder);
        }

        if ($subfolder && $subfolder[0] === DIRECTORY_SEPARATOR) {
            throw new TranslationsManagerException('You can only reference translations folders using a relative path.');
        }

        $filesystem = $this->getLocaleFilesystem($locale);

        if ($subfolder && ! $filesystem->exists($subfolder)) {
            throw new TranslationsManagerException("The translations directory '{$subfolder}' does not exist");
        }
                      
        $files = $filesystem->allFiles($subfolder);

        return array_map(function ($file) {
            $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);

            return preg_replace('/\.php$/', '', $file);
        }, $files);
    }

    /**
     * Get a filesystem with its root set to the directory of a locale;
     *
     * @param string $locale
     * @return Filesystem
     * @throws TranslationsManagerException
     * @throws InvalidArgumentException
     */
    protected function getLocaleFilesystem(string $locale): Filesystem
    {
        $folder = $this->langDirectory.DIRECTORY_SEPARATOR.$locale;
        
        if (! file_exists($folder)) {
            throw new TranslationsManagerException("The translations folder '{$locale}' does not exist");
        }
   
        return $this->manager->createLocalDriver(['root' => $folder]);
    }
}
