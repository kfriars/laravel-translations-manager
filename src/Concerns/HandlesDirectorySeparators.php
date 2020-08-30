<?php

namespace Kfriars\TranslationsManager\Concerns;

trait HandlesDirectorySeparators
{
    /**
     * Ensure the filepath uses the correct directory separator
     *
     * @param null|string $filepath
     * @return null|string
     */
    protected function convertDirectorySeparators(?string $filepath): ?string
    {
        if ($this->isBackslashDirectorySeparator()) {
            return str_replace("/", "\\", $filepath);
        }

        return str_replace("\\", "/", $filepath);
    }

    /**
     * Determine if the DIRECTORY separator is backslash
     *
     * @return bool
     */
    protected function isBackslashDirectorySeparator(): bool
    {
        return DIRECTORY_SEPARATOR === "\\";
    }
}
