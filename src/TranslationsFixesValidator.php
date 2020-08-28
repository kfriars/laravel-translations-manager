<?php

namespace Kfriars\TranslationsManager;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Translation\Translator;
use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Contracts\FixesValidatorContract;
use Kfriars\TranslationsManager\Contracts\ListingContract;
use Kfriars\TranslationsManager\Entities\Error;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsFixesValidator implements FixesValidatorContract
{
    /** @var Filesystem */
    private $fixedFiles;

    /** @var Translator */
    private $translator;

    /** @var string */
    private $fixedDir;

    /** @var string */
    private $referenceLocale;

    /** @var string[] */
    private $supportedLocales;

    public function __construct(
        FilesystemManager $manager,
        ConfigContract $config,
        Translator $translator
    ) {
        $this->fixedDir = config('translations-manager.fixed_dir');
        $this->fixedFiles = $manager->createLocalDriver(['root' => $this->fixedDir]);
        
        $this->translator = $translator;

        $this->referenceLocale = $config->referenceLocale();
        $this->supportedLocales = $config->supportedLocales();
    }

    /**
     * Ensure there are no critical errors that should cause aborting to fail
     *
     * @param ListingContract $listing
     * @return void
     * @throws TranslationsManagerException
     */
    public function validateGenerate(ListingContract $listing): void
    {
        $abortErrors = $listing->errors()->whereIn('message', Error::REFERENCE_FILE_MISSING);

        if ($abortErrors->isNotEmpty()) {
            $mapped = $abortErrors->map(function ($error) {
                return $error->locale().'/'.$error->file();
            })->toArray();

            $message = implode("', '", $mapped);

            throw new TranslationsManagerException(
                "You are attempting to create fix file(s) for '{$message}' but they are not in the reference locale."
            );
        }
    }

    /**
     * Validate that the fix files are valid and they agree with the state of the
     * reference locale
     *
     * @param array $locales
     * @return array
     * @throws TranslationsManagerException
     * @throws FileNotFoundException
     */
    public function validateFixes(array $locales): array
    {
        $files = $this->fixedFiles->files();

        $this->notFixingReferenceLocales($locales);

        $this->allLocalesAreSupported($locales);

        $this->allFixFilesAreNamedProperly($files);

        $unique = $this->onlyOneFixFilePerLocale($locales, $files);

        $this->allLocalesHaveAFixFile($locales, $unique);

        foreach ($unique as $locale => $file) {
            $this->validateFiles($locale, $file);
        }

        return $unique;
    }

    /**
     * Ensure they are not trying to fix the reference locale
     *
     * @param array $locales
     * @return void
     * @throws TranslationsManagerException
     */
    protected function notFixingReferenceLocales(array $locales)
    {
        if (in_array($this->referenceLocale, $locales)) {
            throw new TranslationsManagerException(
                "You cannot fix the reference locale '{$this->referenceLocale}'"
            );
        }
    }

    /**
     * Ensure all locales being fixed are in the configured supported locales
     *
     * @param array $locales
     * @return void
     * @throws TranslationsManagerException
     */
    protected function allLocalesAreSupported(array $locales)
    {
        $missing = array_diff($locales, $this->supportedLocales);
        $missing = implode(', ', $missing);

        if ($missing) {
            throw new TranslationsManagerException(
                "You cannot fix the locale(s) '{$missing}' as they are not supported locale(s)."
            );
        }
    }

    /**
     * Ensure all fix files in the fixed directory are named correctly
     *
     * @param array $files
     * @return void
     * @throws TranslationsManagerException
     */
    protected function allFixFilesAreNamedProperly(array $files)
    {
        foreach ($files as $filepath) {
            $parts = explode('-', $filepath);

            if (count($parts) < 3 || $parts[0] !== 'fixes') {
                throw new TranslationsManagerException(
                    "The fix file '{$filepath}' is not in the correct format 'fixes-{locale}-{date|git_branch}.json'"
                );
            }
        }
    }

    /**
     * Ensure there is only one fix file in the directory per locale. This is to help avoid human error.
     *
     * @param array $locales
     * @param array $files
     * @return array
     * @throws TranslationsManagerException
     */
    protected function onlyOneFixFilePerLocale(array $locales, array $files): array
    {
        $unique = [];

        foreach ($files as $filepath) {
            $parts = explode('-', $filepath);

            if (! in_array($parts[1], $locales)) {
                continue;
            }

            $locale = $parts[1];

            if (isset($unique[$locale])) {
                throw new TranslationsManagerException(
                    "You have multiple fix files for the locale '{$locale}' in the directory '{$this->fixedDir}'."
                );
            }

            $unique[$locale] = $filepath;
        }

        return $unique;
    }

    /**
     * Ensure all of the locales being fixed do in fact have a fix file
     *
     * @param array $locales
     * @param array $unique
     * @return void
     * @throws TranslationsManagerException
     */
    protected function allLocalesHaveAFixFile(array $locales, array $unique)
    {
        $found = array_keys($unique);
        if (count(($missing = array_diff($locales, $found)))) {
            throw new TranslationsManagerException(
                "No fix files were found in '{$this->fixedDir}' for the following locales '".implode("', '", $missing)."'."
            );
        }
    }

    /**
     * Ensure that every fix in every file has a corresponding key in the reference
     * locale. This ensures nothing dangerous has changed since the files were
     * generated, or some human error wasnt made by the translator.
     *
     * @param string $locale
     * @param string $file
     * @return void
     * @throws FileNotFoundException
     * @throws TranslationsManagerException
     */
    protected function validateFiles(string $locale, string $file)
    {
        $content = $this->fixedFiles->get($file);
        $fixed = json_decode($content, true);

        if ($fixed === null) {
            throw new TranslationsManagerException(
                "The file '{$file}' does not contain well formed JSON."
            );
        }

        foreach ($fixed['files'] as $file) {
            if (! $this->translator->has($file['file'], $fixed['reference'])) {
                throw new TranslationsManagerException(
                    "There is no translation file '{$file['file']}' in the reference language."
                );
            }

            $reference = $this->translator->get($file['file'], [], $this->referenceLocale);

            $missing = $this->validateKeys($file, $reference);

            if (count($missing)) {
                $file = $locale.'/'.$file['file'];
                $keys = implode("', '", $missing);

                throw new TranslationsManagerException(
                    "In the file '{$file}' the keys '{$keys}' could not be found in the reference locale."
                );
            }
        }
    }

    /**
     * Validate every key in the fixed file
     *
     * @param array $file
     * @param array $reference
     * @return array
     */
    protected function validateKeys(array $file, array $reference): array
    {
        $missing = [];
        $keys = array_keys($file['translations']);

        foreach ($keys as $key) {
            if ($this->recursiveValidateKey($key, $reference)) {
                $missing[] = $key;
            }
        }

        return $missing;
    }

    /**
     * Since some translations files use dot notation in their keys (ie. laravel custom validation
     * translation files), we can not simply use Arr::has/get/set to work on the files, since dot
     * notation becomes ambiguos.
     *
     * ie) $a = [
     *         "a.b" => [
     *             "c.d" => "dots",
     *         ],
     *         "a" => [
     *             "b" => [
     *                 "c" => [
     *                     "d" => "no dots",
     *                  ],
     *              ],
     *         ],
     *     ];
     *
     *     Arr::dot($a) --> [ "a.b.c.d" => "no dots" ]
     *
     * The best strategy I can think of is to continuosly match the maximum length of the key,
     * until one of the chunks works.
     *
     * ie) for the key, 'first.second.third'
     *     We first check the base of the array for the key 'first.second.third'
     *     then 'first.second' if found, look for 'third' in 'first.second'
     *     then 'first' if found, look for 'second.third' in first'
     *
     * @param string $key
     * @param array $reference
     * @return bool
     */
    protected function recursiveValidateKey(string $key, array $reference): bool
    {
        // Check if we have a full match of the key in the reference locale
        if (isset($reference[$key])) {
            return false;
        }

        $parts = explode('.', $key);
        $next = '';

        while (count($parts) > 1) {
            $next = $next ? array_pop($parts).'.'.$next : array_pop($parts);
            $current = implode('.', $parts);
            
            // If we dont match the key, try a smaller key
            if (! isset($reference[$current])) {
                continue;
            }

            // Since we did not get a full match of the key, each layer down must be an array
            // or something is wrong
            if (! is_array($reference[$current])) {
                return true;
            }
            
            // We have a key match, but it wasnt a full match, so recurse down
            return $this->recursiveValidateKey($next, $reference[$current]);
        }

        return true;
    }
}
