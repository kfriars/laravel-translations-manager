<?php

namespace Kfriars\TranslationsManager\Contracts;

interface IgnoresContract
{
    /**
     * Determine if errors for a given key should be ignored
     *
     * @param string $locale
     * @param string $file
     * @param null|string $key
     * @return bool
     */
    public function isIgnored(string $locale, string $file, ?string $key = null): bool;

    /**
     * Ignore errors from a file or a key
     *
     * @param string $locale
     * @param string $file
     * @param null|string $key
     * @return void
     */
    public function ignore(string $locale, string $file, ?string $key = null): void;

    /**
     * Unignore errors from a file or a key
     *
     * @param string $locale
     * @param string $file
     * @param null|string $key
     * @return void
     */
    public function unignore(string $locale, string $file, ?string $key = null): void;


    /**
     * Get all ignores
     *
     * @return array
     */
    public function all(): array;
}
