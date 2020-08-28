<?php

namespace Kfriars\TranslationsManager\Entities;

use Kfriars\TranslationsManager\Contracts\ErrorContract;
use ReflectionClass;
use ReflectionProperty;

class Error implements ErrorContract
{
    const TRANSLATION_MISSING = 'translation_missing';
    const FILE_NOT_TRANSLATED = 'file_not_translated';
    const REFERENCE_FILE_MISSING = 'reference_file_missing';
    const NO_REFERENCE_TRANSLATION = 'no_reference_translation';
    const REFERENCE_TRANSLATION_UPDATED = 'reference_translation_updated';
    const INCORRECT_TRANSLATION_TYPE = 'incorrect_translation_type';

    /** @var bool */
    protected $ignored;

    /** @var string */
    protected $locale;

    /** @var string */
    protected $file;

    /** @var string */
    protected $message;

    /** @var string */
    protected $key;

    public function __construct(
        string $locale,
        string $file,
        string $key,
        string $message,
        bool $ignored
    ) {
        $this->locale = $locale;
        $this->file = $file;
        $this->message = $message;
        $this->key = $key;
        $this->ignored = $ignored;
    }

    /**
     * The locale of the error
     *
     * @return string
     */
    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * The file of the error
     *
     * @return string
     */
    public function file(): string
    {
        return $this->file;
    }

    /**
     * The key of the error
     *
     * @return string
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * Get the full key the translation can be referenced using __()
     *
     * @return string
     */
    public function fullKey(): string
    {
        return $this->file.'.'.$this->key;
    }

    /**
     * The message of the error
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * Determine if the error is ignored
     *
     * @return bool
     */
    public function ignored(): bool
    {
        return $this->ignored;
    }

    /**
     * Determine if the error is critical
     *
     * @return bool
     */
    public function critical(): bool
    {
        return ! in_array($this->message, [
            static::NO_REFERENCE_TRANSLATION,
            static::REFERENCE_TRANSLATION_UPDATED,
        ]);
    }

    /**
     * Check if an array offset exists, we are implementing ArrayAccess so the errors
     * play nicely with Laravel's collections
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED);
        $properties = array_map(function (ReflectionProperty $property) {
            return $property->getName();
        }, $properties);

        return in_array($offset, $properties);
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
     * Get an array representaion of the error
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'locale' => $this->locale,
            'file' => $this->file,
            'key' => $this->key,
            'message' => $this->message,
            'ignored' => $this->ignored,
        ];
    }
}
