<?php

namespace Kfriars\TranslationsManager;

use Kfriars\ArrayToFile\Exceptions\FileSaveException;
use Kfriars\TranslationsManager\Contracts\FileWriterContract;
use Kfriars\TranslationsManager\Contracts\LockfilesContract;

class TranslationsLockfiles implements LockfilesContract
{
    /** @var string */
    protected $referenceLocale;

    /** @var string */
    protected $lockDirectory;
    
    /** @var FileWriterContract */
    protected $writer;

    public function __construct(FileWriterContract $writer)
    {
        $this->writer = $writer;
        $this->referenceLocale = config('translations-manager.reference_locale');
        $this->lockDirectory = config('translations-manager.lock_dir');
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
        
        $this->writer->writeArray($reference, $filename);
    }

    /**
     * Get the path to a lockfile, for a given langfile
     *
     * @param string $langFile
     * @return string
     */
    public function lockpath(string $langFile): string
    {
        if (DIRECTORY_SEPARATOR === "\\") {
            $langFile = str_replace("/", DIRECTORY_SEPARATOR, $langFile);
        }
        
        return $this->lockDirectory.DIRECTORY_SEPARATOR.$langFile.'.php';
    }
}
