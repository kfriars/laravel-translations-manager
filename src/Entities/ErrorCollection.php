<?php

namespace Kfriars\TranslationsManager\Entities;

use Illuminate\Support\Collection;
use Kfriars\TranslationsManager\Contracts\ErrorContract;

class ErrorCollection extends Collection
{
    /**
     * @return ErrorContract[]
     */
    public function all()
    {
        return parent::all();
    }

    /**
     * Get the first error in the collection
     *
     * @param null|callable $callback
     * @param mixed $default
     * @return ErrorContract
     */
    public function first(?callable $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }
}
