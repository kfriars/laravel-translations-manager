<?php

namespace Kfriars\TranslationsManager\Entities;

use Illuminate\Support\Collection;
use Kfriars\TranslationsManager\Contracts\LocaleContract;

class LocaleCollection extends Collection
{
    /**
     * Get all errors in the collection
     *
     * @return LocaleContract[]
     */
    public function all()
    {
        return parent::all();
    }

    /**
     * Get the first locale in the collection
     *
     * @param null|callable $callback
     * @param mixed $default
     * @return LocaleContract
     */
    public function first(?callable $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }
}
