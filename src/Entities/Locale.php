<?php

namespace Kfriars\TranslationsManager\Entities;

use Kfriars\TranslationsManager\Contracts\LocaleContract;
use Kfriars\TranslationsManager\Contracts\TranslationsFileContract;

class Locale implements LocaleContract
{
    /** @var string */
    protected $code;
    
    /** @var TranslationsFileCollection */
    protected $files;

    /**
     * @param string $code
     * @param TranslationsFile[] $files
     * @return void
     */
    public function __construct(string $code)
    {
        $this->files = new TranslationsFileCollection();
        $this->code = $code;
    }

    /**
     * The code of the locale (ie. en, de, fr)
     *
     * @return string
     */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * Get all files for the listing of the locale
     *
     * @return TranslationsFileCollection
     */
    public function files(): TranslationsFileCollection
    {
        return $this->files;
    }

    /**
     * Add a lie to the locale
     *
     * @param TranslationsFileContract $file
     * @return void
     */
    public function addFile(TranslationsFileContract $file): void
    {
        $this->files->push($file);
    }

    /**
     * Get all of the errors in the locale, if ignore is set, only return errors that are not ignored
     *
     * @param bool $ignore
     * @return ErrorCollection
     */
    public function errors(bool $ignore = true): ErrorCollection
    {
        $errors = new ErrorCollection();

        foreach ($this->files->all() as $file) {
            foreach ($file->errors($ignore) as $error) {
                $errors->push($error);
            }
        }

        return $errors;
    }

    /**
     * Get all of the errors in the locale, if ignore is set, only return errors that are not ignored
     *
     * @param bool $ignore
     * @return ErrorCollection
     */
    public function critical(bool $ignore = true): ErrorCollection
    {
        $errors = new ErrorCollection();

        foreach ($this->files->all() as $file) {
            foreach ($file->critical($ignore) as $error) {
                $errors->push($error);
            }
        }

        return $errors;
    }

    /**
     * Determine if the locale has any errors, if ignore is set, errors can be ignored
     *
     * @param bool $ignore
     * @return bool
     */
    public function hasErrors(bool $ignore = true): bool
    {
        foreach ($this->files as $file) {
            if ($file->hasErrors($ignore)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an array offset exists, we are implementing ArrayAccess so the locales
     * play nicely with Laravel's collections
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $offset === 'code';
    }

    /**
     * Get the array offest property
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Set the property using array access
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Unset the property using array access
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }

    /**
     * Get an array representation of the Locale
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'locale' => $this->code,
            'files' => $this->files->values()->toArray(),
        ];
    }
}
