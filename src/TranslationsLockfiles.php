<?php

namespace Kfriars\TranslationsManager;

use Kfriars\ArrayToFile\Exceptions\FileSaveException;
use Kfriars\TranslationsManager\Concerns\HandlesDirectorySeparators;
use Kfriars\TranslationsManager\Contracts\ArrayFileContract;
use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Contracts\LockfilesContract;

class TranslationsLockfiles implements LockfilesContract
{
    use HandlesDirectorySeparators;
    
    /** @var string */
    protected $referenceLocale;

    /** @var string */
    protected $lockDirectory;
    
    /** @var ArrayFileContract */
    protected $arrayFile;

    public function __construct(
        ConfigContract $config,
        ArrayFileContract $arrayFile
    ) {
        $this->arrayFile = $arrayFile;
        $this->referenceLocale = $config->referenceLocale();
        $this->lockDirectory = $config->lockDir();
    }

    /**
     * Get the locked version of the translations file for the given locale
     *
     * @param string $langFile
     * @return array
     * @throws FileSaveException
     */
    public function getLockfile(string $langFile): array
    {
        $lockpath = $this->lockpath($langFile);

        if (! file_exists($lockpath)) {
            $this->lockLangFile($langFile);
        }

        $lock = include $lockpath;

        return $lock;
    }

    /**
     * * Write the lock file to the reference lanuage's current state of the file
     *
     * @param string $langFile
     * @return void
     * @throws FileSaveException
     */
    public function lockLangFile(string $langFile): void
    {
        $reference = __($langFile, [], $this->referenceLocale);
        $filename = $this->lockpath($langFile);
        
        $this->arrayFile->write($reference, $filename);
    }

    /**
     * Get the path to a lockfile, for a given langfile
     *
     * @param string $langFile
     * @return string
     */
    public function lockpath(string $langFile): string
    {
        $langFile = $this->convertDirectorySeparators($langFile);
        
        return $this->lockDirectory.DIRECTORY_SEPARATOR.$langFile.'.php';
    }
}
