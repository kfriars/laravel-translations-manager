<?php

namespace Kfriars\TranslationsManager;

use Kfriars\ArrayToFile\ArrayToFile;
use Kfriars\ArrayToFile\Exceptions\FileSaveException;
use Kfriars\TranslationsManager\Concerns\HandlesDirectorySeparators;
use Kfriars\TranslationsManager\Contracts\ArrayFileContract;

class TranslationsArrayFile implements ArrayFileContract
{
    use HandlesDirectorySeparators;
    
    /** @var ArrayToFile */
    protected $a2f;

    public function __construct(ArrayToFile $a2f)
    {
        $this->a2f = $a2f;
    }

    /**
     * Write an array to an includeable php file
     *
     * @param array $array
     * @param string $filepath
     * @param callable|null $transform
     * @return void
     * @throws FileSaveException
     */
    public function write(array $array, string $filepath, callable $transform = null): void
    {
        $filepath = $this->convertDirectorySeparators($filepath);

        $this->a2f->write($array, $filepath, $transform);
    }
}
