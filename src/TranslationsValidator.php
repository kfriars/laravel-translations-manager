<?php

namespace Kfriars\TranslationsManager;

use Illuminate\Translation\Translator;
use Kfriars\TranslationsManager\Contracts\ErrorContract;
use Kfriars\TranslationsManager\Contracts\IgnoresContract;
use Kfriars\TranslationsManager\Contracts\ListingContract;
use Kfriars\TranslationsManager\Contracts\LockfilesContract;
use Kfriars\TranslationsManager\Contracts\ValidatorContract;
use Kfriars\TranslationsManager\Entities\Error;
use Kfriars\TranslationsManager\Entities\Listing;
use Kfriars\TranslationsManager\Entities\Locale;
use Kfriars\TranslationsManager\Entities\TranslationsFile;

class TranslationsValidator implements ValidatorContract
{
    /** @var LockfilesContract */
    protected $lockfiles;

    /** @var IgnoresContract */
    protected $ignores;

    /** @var Translator */
    protected $translator;
    
    public function __construct(
        LockfilesContract $lockfiles,
        IgnoresContract $ignores,
        Translator $translator
    ) {
        $this->lockfiles = $lockfiles;
        $this->ignores = $ignores;
        $this->translator = $translator;
    }

    /**
     * Compare all supported lang files in the listing against reference language's lang files
     *
     * @param string $referenceLocale
     * @param array $files
     * @param array $locales
     * @return ListingContract
     */
    public function validate(string $referenceLocale, array $files, array $locales): ListingContract
    {
        $listing = new Listing($referenceLocale);

        foreach ($locales as $code) {
            $locale = new Locale($code);
            $listing->addLocale($locale);

            foreach ($files as $path) {
                $ignored = $this->ignores->isIgnored($code, $path);
                $file = new TranslationsFile($path, $ignored);
                $locale->addFile($file);

                $file->addErrors($this->validateTranslations($path, $referenceLocale, $code));
            }
        }

        return $listing;
    }

    /**
     * Validate the reference and dependent lang file
     *
     * @param string $langFile
     * @param string $referenceLocale
     * @param string $locale
     * @return ErrorContract[]
     */
    protected function validateTranslations(string $langFile, string $referenceLocale, string $locale): array
    {
        if (! $this->translator->has($langFile, $locale)) {
            return [new Error(
                $locale,
                $langFile,
                'FILE_ERROR',
                Error::FILE_NOT_TRANSLATED,
                $this->ignores->isIgnored($locale, $langFile)
            )];
        }

        $reference = $this->translator->get($langFile, [], $referenceLocale);

        if (! is_array($reference)) {
            return [new Error(
                $locale,
                $langFile,
                'FILE_ERROR',
                Error::REFERENCE_FILE_MISSING,
                $this->ignores->isIgnored($locale, $langFile)
            )];
        }

        $dependent = $this->translator->get($langFile, [], $locale);
        $lockfile = $this->lockfiles->getLockfile($langFile);
        $errors = $this->recursiveValidateTranslations($reference, $dependent, $lockfile);

        foreach ($errors as $idx => $error) {
            $errors[$idx] = new Error(
                $locale,
                $langFile,
                $error['key'],
                $error['message'],
                $this->ignores->isIgnored($locale, $langFile, $error['key'])
            );
        }

        return $errors;
    }

    /**
     * Recursively validate all keys and translations for a given lang file
     *
     * @param array $reference
     * @param array $dependent
     * @param array $lockfile
     * @param string $keyPath
     * @param array $errors
     * @return array
     */
    protected function recursiveValidateTranslations(
        array $reference,
        array $dependent,
        array $lockfile,
        string $keyPath = '',
        array &$errors = []
    ): array {
        $this->makeReferenceAssertions($reference, $dependent, $lockfile, $keyPath, $errors);
        $this->makeDependentAssertions($dependent, $reference, $keyPath, $errors);

        foreach ($reference as $key => $value) {
            if (! is_array($value) || ! isset($dependent[$key])) {
                continue;
            }

            if (! is_array($dependent[$key])) {
                $path = $keyPath ? "$keyPath.$key" : $key;
                $errors[] = [
                    'key' => $path,
                    'message' => Error::INCORRECT_TRANSLATION_TYPE,
                ];

                continue;
            }

            $path = $keyPath ? "$keyPath.$key" : $key;
            $this->recursiveValidateTranslations(
                $reference[$key],
                $dependent[$key],
                $lockfile[$key] ?? [],
                $path,
                $errors
            );

            if (empty($errors[$path])) {
                unset($errors[$path]);
            }
        }

        return $errors;
    }

    /**
     * Make assertions about what keys should be on the dependent language, based on the
     * keys in the reference language. Also check to make sure the reference language has not
     * been updated since last being locked.
     *
     * @param array $reference
     * @param array $dependent
     * @param array $locked
     * @param string $keyPath
     * @param array $errors
     * @return void
     */
    protected function makeReferenceAssertions(
        array $reference,
        array $dependent,
        array $locked,
        string $keyPath,
        array &$errors
    ): void {
        foreach ($reference as $key => $value) {
            $path = $keyPath ? "$keyPath.$key" : $key;

            if (! isset($dependent[$key])) {
                $errors[] = [
                    'key' => $path,
                    'message' => Error::TRANSLATION_MISSING,
                ];
            } elseif (! is_array($value) && is_array($dependent[$key])) {
                $errors[] = [
                    'key' => $path,
                    'message' => Error::INCORRECT_TRANSLATION_TYPE,
                ];
            } elseif (isset($locked[$key]) && ! is_array($value) && $value !== $locked[$key]) {
                $errors[] = [
                    'key' => $path,
                    'message' => ERROR::REFERENCE_TRANSLATION_UPDATED,
                ];
            }
        }
    }

    /**
     * Make assertions about what keys should be on the reference language, based on the
     * keys in the dependent language
     *
     * @param array $reference
     * @param array $dependent
     * @param array $locked
     * @param string $keyPath
     * @param array $errors
     * @return void
     */
    protected function makeDependentAssertions(
        array $dependent,
        array $reference,
        string $keyPath,
        array &$errors
    ): void {
        foreach (array_keys($dependent) as $key) {
            if (! isset($reference[$key])) {
                $path = $keyPath ? "$keyPath.$key" : $key;

                $errors[] = [
                    'key' => $path,
                    'message' => Error::NO_REFERENCE_TRANSLATION,
                ];
            }
        }
    }
}
