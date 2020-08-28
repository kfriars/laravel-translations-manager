<?php

namespace Kfriars\TranslationsManager;

use Kfriars\TranslationsManager\Contracts\FileWriterContract;
use Kfriars\TranslationsManager\Contracts\IgnoresContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsIgnores implements IgnoresContract
{
    /** @var string */
    protected $ignoresFile;

    /** @var FileWriterContract */
    protected $writer;
    
    /** @var array */
    protected $ignores;

    public function __construct(
        FileWriterContract $writer
    ) {
        $this->writer = $writer;
        $this->ignoresFile = config('translations-manager.ignores');
        $this->ignores = $this->ignoresFromFile($this->ignoresFile);
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

        $this->writer->writeArray($this->ignores, $this->ignoresFile);
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

        $this->writer->writeArray($this->ignores, $this->ignoresFile);
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
    protected function ignoresFromFile(?string $filepath): array
    {
        if ($filepath === null) {
            throw new TranslationsManagerException("You do not have 'ignores' configured for translations-manager.");
        }

        if (! file_exists($this->ignoresFile)) {
            $this->writer->writeArray([], $filepath);
        }

        return (include $this->ignoresFile);
    }
}
