<?php

namespace Kfriars\TranslationsManager\Contracts;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ErrorContract extends Arrayable, ArrayAccess
{
    /**
     * The locale of the error
     *
     * @return string
     */
    public function locale(): string;

    /**
     * The file of the error
     *
     * @return string
     */
    public function file(): string;

    /**
     * The key of the error
     *
     * @return string
     */
    public function key(): string;

    /**
     * Get the full key the translation can be referenced using __()
     *
     * @return string
     */
    public function fullKey(): string;

    /**
     * The message of the error
     *
     * @return string
     */
    public function message(): string;

    /**
     * Determine if the error is ignored
     *
     * @return bool
     */
    public function ignored(): bool;

    /**
     * Determine if the error is critical
     *
     * @return bool
     */
    public function critical(): bool;
}
