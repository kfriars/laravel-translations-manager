<?php

namespace Kfriars\TranslationsManager\Contracts;

use Kfriars\ArrayToFile\Exceptions\FileSaveException;

interface FileWriterContract
{
    /**
     * Write an array to an includeable php file
     *
     * @param array $array
     * @param string $filepath
     * @param callable|null $transform
     * @return void
     * @throws FileSaveException
     */
    public function writeArray(array $array, string $filepath, callable $transform = null): void;
}
