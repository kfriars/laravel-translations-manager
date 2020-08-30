<?php

namespace Kfriars\TranslationsManager;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Contracts\FormatterContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsFixesJSONFormatter implements FormatterContract
{
    /** @var Filesystem */
    protected $fixes;

    /** @var Filesystem */
    protected $fixed;

    public function __construct(
        ConfigContract $config
    ) {
        $this->fixes = $config->fixes();
        $this->fixed = $config->fixed();
    }

    /**
     * Format and write the array of fixes to a file
     *
     * @param string $filename
     * @param array $fixes
     * @return void
     */
    public function write(string $filename, array $fixes): void
    {
        $this->fixes->put(
            $filename,
            json_encode($fixes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Read a formatted array of fixes from a file
     *
     * @param string $filename
     * @return array
     * @throws FileNotFoundException
     * @throws TranslationsManagerException
     */
    public function read(string $filename): array
    {
        $content = $this->fixed->get($filename);

        $fixed = json_decode($content, true);

        if ($fixed === null) {
            throw new TranslationsManagerException(
                "The file '{$filename}' does not contain well formed JSON."
            );
        }

        return $fixed;
    }
}
