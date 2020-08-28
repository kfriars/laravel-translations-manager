<?php

namespace Kfriars\TranslationsManager\Contracts;

use Kfriars\ArrayToFile\Exceptions\FileSaveException;

interface LockfilesContract
{
    /**
     * Get the locked version of the translations file
     *
     * @param string $langFile
     * @return array
     * @throws FileSaveException
     */
    public function getLockfile(string $langFile): array;

    /**
     * Write the lock file to the reference lanuage's current state of the file
     *
     * @param string $langFile
     * @return void
     * @throws FileSaveException
     */
    public function lockLangFile(string $langFile): void;

    /**
     * Get the path to a lockfile, for a given langfile
     *
     * @param string $langFile
     * @return string
     */
    public function lockpath(string $langFile): string;
}
