<?php

namespace Kfriars\TranslationsManager\Contracts;

interface FormatterContract
{
    /**
     * Format and write the array of fixes to a file
     *
     * @param string $filename
     * @param array $fixes
     * @return void
     */
    public function write(string $filename, array $fixes): void;
    
    /**
     * Read a formatted array of fixes from a file
     *
     * @param string $filename
     * @return array
     * @throws FileNotFoundException
     * @throws TranslationsManagerException
     */
    public function read(string $filename): array;
}
