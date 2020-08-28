<?php

namespace Kfriars\TranslationsManager\Contracts;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Kfriars\TranslationsManager\Entities\ErrorCollection;

interface TranslationsFileContract extends Arrayable, ArrayAccess
{
    /**
     * Get the translation file path (without file extension)
     *
     * @return string
     */
    public function path(): string;
    
    /**
     * Determine if errors from this file should be ignored
     *
     * @return bool
     */
    public function ignored(): bool;
    
    /**
     * Get all of the files errors, if ignore is set, only return errors that are not ignored
     *
     * @return ErrorCollection
     */
    public function errors(bool $ignore = true): ErrorCollection;

    /**
     * Get all of the files critical errors, if ignore is set, only return errors that are not ignored
     *
     * @return ErrorCollection
     */
    public function critical(bool $critical = true): ErrorCollection;
    
    /**
     * Add errors to the file
     * @param array $errors
     *
     * @return void
     */
    public function addErrors(array $errors): void;
    
    /**
     * Add an error to the file
     *
     * @param ErrorContract $error
     * @return void
     */
    public function addError(ErrorContract $error): void;
    
    /**
     * Determine if the file has any errors
     *
     * @param bool $ignore
     * @return bool
     */
    public function hasErrors(bool $ignore = true): bool;
}
