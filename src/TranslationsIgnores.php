<?php

namespace Kfriars\TranslationsManager;

use Kfriars\TranslationsManager\Contracts\ArrayFileContract;
use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Contracts\IgnoresContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsIgnores implements IgnoresContract
{
    /** @var string */
    protected $ignoresFile;

    /** @var ArrayFileContract */
    protected $arrayFile;
    
    /** @var array */
    protected $ignores;

    public function __construct(
        ConfigContract $config,
        ArrayFileContract $arrayFile
    ) {
        $this->arrayFile = $arrayFile;
        $this->ignoresFile = $config->ignoresPath();
        $this->ignores = $this->ignoresFromFile();
    }

    /**
     * Check if errors for a file/key should be ignored
     *
     * @param string $locale
     * @param string $file
     * @param null|string $key
     * @return bool
     */
    public function isIgnored(string $locale, string $file, ?string $key = null): bool
    {
        if (isset($this->ignores[$locale][$file]) && $this->ignores[$locale][$file] === true) {
            return true;
        }

        if ($key === null) {
            return false;
        }
        
        return isset($this->ignores[$locale][$file][$key]) && $this->ignores[$locale][$file][$key] === true;
    }

    /**
     * Ignore errors from a file or a key
     *
     * @param string $locale
     * @param string $file
     * @param null|string $key
     * @return void
     */
    public function ignore(string $locale, string $file, ?string $key = null): void
    {
        if ($this->isIgnored($locale, $file, $key)) {
            return;
        }

        if ($key === null) {
            $this->ignores[$locale][$file] = true;
        } else {
            $this->ignores[$locale][$file][$key] = true;
        }

        $this->arrayFile->write($this->ignores, $this->ignoresFile);
    }

    /**
     * Unignore errors from a file or a key
     *
     * @param string $locale
     * @param string $file
     * @param null|string $key
     * @return void
     */
    public function unignore(string $locale, string $file, ?string $key = null): void
    {
        if (! $this->isIgnored($locale, $file, $key)) {
            return;
        }

        if ($key === null) {
            unset($this->ignores[$locale][$file]);
        } else {
            unset($this->ignores[$locale][$file][$key]);
        }

        if (empty($this->ignores[$locale][$file])) {
            unset($this->ignores[$locale][$file]);
        }

        if (empty($this->ignores[$locale])) {
            unset($this->ignores[$locale]);
        }

        $this->arrayFile->write($this->ignores, $this->ignoresFile);
    }

    /**
     * Get all ignores
     *
     * @return array
     */
    public function all(): array
    {
        return $this->ignores;
    }

    /**
     * Load the ignores from the ignores file
     *
     * @param null|string $filepath
     * @return array
     * @throws TranslationsManagerException
     */
    protected function ignoresFromFile(): array
    {
        if (! file_exists($this->ignoresFile)) {
            $this->arrayFile->write([], $this->ignoresFile);
        }

        return (include $this->ignoresFile);
    }
}
