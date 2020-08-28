<?php

namespace Kfriars\TranslationsManager\Entities;

use Kfriars\TranslationsManager\Contracts\ErrorContract;
use Kfriars\TranslationsManager\Contracts\TranslationsFileContract;

class TranslationsFile implements TranslationsFileContract
{
    /** @var ErrorCollection */
    protected $errors;

    /** @var string */
    protected $path;

    /** @var bool */
    protected $ignored;

    public function __construct(string $path, bool $ignored = false)
    {
        $this->path = $path;
        $this->ignored = $ignored;
        $this->errors = new ErrorCollection();
    }

    /**
     * Get the translation file path (without file extension)
     *
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Determine if errors from this file should be ignored
     *
     * @return bool
     */
    public function ignored(): bool
    {
        return $this->ignored;
    }

    /**
     * Get all of the files errors, if ignore is set, only return errors that are not ignored
     *
     * @return ErrorCollection
     */
    public function errors(bool $ignore = true): ErrorCollection
    {
        if ($ignore && $this->ignored) {
            return new ErrorCollection();
        }

        if (! $ignore) {
            return $this->errors;
        }

        return $this->errors->filter(function (ErrorContract $error) {
            return ! $error->ignored();
        });
    }

    /**
     * Get all of the files critical errors, if ignore is set, only return errors that are not ignored
     *
     * @return ErrorCollection
     */
    public function critical(bool $ignore = true): ErrorCollection
    {
        if ($ignore && $this->ignored) {
            return new ErrorCollection();
        }

        if (! $ignore) {
            return $this->errors->filter(function (ErrorContract $error) {
                return $error->critical();
            });
        }

        return $this->errors->filter(function (ErrorContract $error) {
            return ! $error->ignored() && $error->critical();
        });
    }

    /**
     * Add errors to the file
     * @param array $errors
     *
     * @return void
     */
    public function addErrors(array $errors): void
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }
    }

    /**
     * Add an error to the file
     *
     * @param ErrorContract $error
     * @return void
     */
    public function addError(ErrorContract $error): void
    {
        $this->errors->push($error);
    }

    /**
     * Determine if the file has any errors
     *
     * @param bool $ignore
     * @return bool
     */
    public function hasErrors(bool $ignore = true): bool
    {
        if ($ignore && $this->ignored) {
            return false;
        }

        foreach ($this->errors->all() as $error) {
            if ($ignore && $error->ignored()) {
                continue;
            }

            return true;
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
        return in_array($offset, ['path', 'ignored']);
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
     * Return an array representation of the file
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'file' => $this->path,
            'ignored' => $this->ignored,
            'errors' => $this->errors->values()->toArray(),
        ];
    }
}
