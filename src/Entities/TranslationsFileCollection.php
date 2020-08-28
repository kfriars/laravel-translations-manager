<?php

namespace Kfriars\TranslationsManager\Entities;

use Illuminate\Support\Collection;
use Kfriars\TranslationsManager\Contracts\TranslationsFileContract;

class TranslationsFileCollection extends Collection
{
    /**
     * Get all errors in the collection
     *
     * @return TranslationsFileContract[]
     */
    public function all()
    {
        return parent::all();
    }

    /**
     * Get the first file in the collection
     *
     * @param null|callable $callback
     * @param mixed $default
     * @return TranslationsFileContract
     */
    public function first(?callable $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }
}
