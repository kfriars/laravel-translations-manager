<?php

namespace Kfriars\TranslationsManager;

use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Translation\Translator;
use Kfriars\ArrayToFile\Exceptions\FileSaveException;
use Kfriars\TranslationsManager\Concerns\HandlesDirectorySeparators;
use Kfriars\TranslationsManager\Contracts\ArrayFileContract;
use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Contracts\FixerContract;
use Kfriars\TranslationsManager\Contracts\FixesValidatorContract;
use Kfriars\TranslationsManager\Contracts\FormatterContract;
use Kfriars\TranslationsManager\Contracts\ListingContract;
use Kfriars\TranslationsManager\Contracts\LocaleContract;
use Kfriars\TranslationsManager\Contracts\LockfilesContract;
use Kfriars\TranslationsManager\Contracts\TranslationsFileContract;
use Kfriars\TranslationsManager\Entities\Error;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;

class TranslationsFixer implements FixerContract
{
    use HandlesDirectorySeparators;

    /** @var FormatterContract */
    protected $formatter;

    /** @var ArrayFileContract */
    protected $arrayFile;

    /** @var FixesValidatorContract */
    protected $validator;

    /** @var LockfilesContract */
    protected $lockfiles;

    /** @var Translator */
    protected $translator;

    /** @var string */
    protected $langDir;

    /** @var string */
    protected $nameFormat;

    public function __construct(
        ConfigContract $config,
        FormatterContract $formatter,
        ArrayFileContract $arrayFile,
        FixesValidatorContract $validator,
        LockfilesContract $lockfiles,
        Translator $translator
    ) {
        $this->formatter = $formatter;
        $this->arrayFile = $arrayFile;
        $this->validator = $validator;
        $this->lockfiles = $lockfiles;
        $this->translator = $translator;

        $this->langDir = $config->langDir();
        $this->nameFormat = $config->fixNameFormat();
    }

    /**
     * Write fix files for all locales in the listing in json format
     *
     * @param ListingContract $listing
     * @return void
     * @throws BindingResolutionException
     */
    public function generateFixFiles(ListingContract $listing): void
    {
        $this->validator->validateGenerate($listing);

        foreach ($listing->locales()->all() as $locale) {
            $translations = $this->generateForLocale($listing->referenceLocale(), $locale);

            $name = $this->filename($locale->code());
            $this->formatter->write($name, $translations);
        }
    }

    /**
     * List all the fixes for a locale
     *
     * @param string $referenceLocale
     * @param LocaleContract $locale
     * @return array
     */
    protected function generateForLocale(string $referenceLocale, LocaleContract $locale): array
    {
        $fixes = [
            'reference' => $referenceLocale,
            'locale' => $locale->code(),
            'files' => [],
        ];

        foreach ($locale->files()->all() as $file) {
            $translations = $this->generateForFile($referenceLocale, $file);
            
            if ($translations) {
                $fixes['files'][] = [
                    'file' => $file->path(),
                    'translations' => $translations,
                ];
            }
        }

        return $fixes;
    }

    /**
     * List all the translations that need to be fixed in a file
     *
     * @param string $referenceLocale
     * @param TranslationsFileContract $file
     * @return array
     */
    protected function generateForFile(string $referenceLocale, TranslationsFileContract $file): array
    {
        if ($file->errors()->contains('message', Error::FILE_NOT_TRANSLATED)) {
            return (array) $this->translator->get($file->path(), [], $referenceLocale);
        }

        $translations = [];

        foreach ($file->errors()->all() as $error) {
            if ($error->message() === Error::NO_REFERENCE_TRANSLATION) {
                continue;
            }

            $translation = $this->translator->get($error->fullKey(), [], $referenceLocale);
            $translations[$error->key()] = $translation;
        }

        return $translations;
    }

    /**
     * Fix a locale's translations using it's fix file
     *
     * @param string $locale
     * @return void
     * @throws TranslationsManagerException
     * @throws FileSaveException
     */
    public function fix(string $locale): void
    {
        $this->fixMany([ $locale ]);
    }

    /**
     * Fix the specified locale's translations using the fix files
     *
     * @param array $locale
     * @return void
     * @throws TranslationsManagerException
     * @throws FileSaveException
     */
    public function fixMany(array $locales): void
    {
        $fixes = $this->validator->validateFixes($locales);

        foreach ($fixes as $locale => $file) {
            $fixed = $this->formatter->read($file);

            $this->healLocale($locale, $fixed);
        }
    }

    /**
     * Heal all translations in a locale using its fix file
     *
     * @param string $locale
     * @param array $fixed
     * @return void
     * @throws TranslationsManagerException
     * @throws FileSaveException
     */
    protected function healLocale(string $locale, array $fixed): void
    {
        foreach ($fixed['files'] as $file) {
            $filepath = $file['file'];

            $reference = $this->translator->get($filepath, [], $fixed['reference']);
            $has = $this->translator->has($filepath, $locale);
            $dependent = $has ? $this->translator->get($filepath, [], $locale) : [];

            $lockfile = $this->lockfiles->getLockfile($filepath);

            $this->healFile($file['translations'], $dependent, $lockfile, $reference);
            $this->arrayFile->write($dependent, $this->langFilePath($locale, $filepath));

            $lockpath = $this->lockfiles->lockpath($filepath);
            $this->arrayFile->write($lockfile, $lockpath);
        }
    }

    /**
     * Heal all translations in a file using the updated translations and the reference locale
     * as a guide to ensure consistency
     *
     * @param array $healing
     * @param array $reference
     * @param array $translated
     * @return void
     * @throws TranslationsManagerException
     */
    protected function healFile(array $translated, array &$healing, array &$lockfile, array $reference): void
    {
        foreach ($translated as $key => $translation) {
            $this->recursiveReplaceKey($key, $translation, $healing, $lockfile, $reference);
        }
    }

    /**
     * Recursively attempt to replace a key in the array
     *
     * @see TranslationsFixesValidator@recursiveValidateKey() for the reasoning behind this approach
     *
     * @param string $key
     * @param string|array $translation
     * @param array &$healing
     * @param array &$reference
     * @return void
     */
    protected function recursiveReplaceKey(
        string $key,
        $translation,
        array &$healing,
        array &$lockfile,
        array $reference
    ): void {
        // Check if we have a full match of the key in the reference locale
        if (isset($reference[$key])) {
            $healing[$key] = $translation;
            $lockfile[$key] = $reference[$key];

            return;
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
            
            // Since we did not get a full match of the key, and the translations in $healing
            // must match those in $reference, then the next level down needs to be an array
            if (! isset($healing[$current]) || ! is_array($healing[$current])) {
                $healing[$current] = [];
            }

            if (! isset($lockfile[$current]) || ! is_array($lockfile[$current])) {
                $lockfile[$current] = [];
            }

            // We have a key match, but it wasnt a full match, so recurse down
            $this->recursiveReplaceKey(
                $next,
                $translation,
                $healing[$current],
                $lockfile[$current],
                $reference[$current]
            );
        }
    }

    /**
     * Clean dead translations from the listing (no_reference_translation)
     *
     * @param ListingContract $listing
     * @return int
     */
    public function clean(ListingContract $listing): int
    {
        $dead = $listing->errors()
                        ->where('message', Error::NO_REFERENCE_TRANSLATION);

        foreach ($dead->groupBy(['locale', 'file']) as $locale => $files) {
            foreach ($files as $file => $errors) {
                $translations = $this->translator->get($file, [], $locale);
                
                foreach ($errors as $error) {
                    $this->recursiveRemoveKey($translations, $error->key());
                }
            }
            
            $filepath = $this->langFilePath($error->locale(), $error->file());
            $this->arrayFile->write($translations, $filepath);
        }

        return count($dead);
    }

    /**
     * Recursively attempt to remove the key from the translations
     *
     * @see TranslationsFixesValidator@recursiveValidateKey() for the reasoning behind this approach
     *
     * @param array $translations
     * @param string $key
     * @return void
     */
    protected function recursiveRemoveKey(array &$translations, string $key): void
    {
        if (isset($translations[$key])) {
            unset($translations[$key]);

            return;
        }

        $parts = explode('.', $key);
        $next = '';

        while (count($parts) > 1) {
            $next = $next ? array_pop($parts).'.'.$next : array_pop($parts);
            $current = implode('.', $parts);

            // If we dont match the key, try a smaller key
            if (! isset($translations[$current])) {
                continue;
            }
            
            // We have a key match, but it wasnt a full match, so recurse down
            $this->recursiveRemoveKey(
                $translations[$current],
                $next
            );

            // If we reoved the last key from this level of nesting we can unset it
            if (empty($translations[$next])) {
                unset($translations[$next]);
            }
        }
    }

    /**
     * Get the path to the lang file
     *
     * @param string $locale
     * @param string $file
     * @return string
     */
    protected function langFilePath(string $locale, string $file): string
    {
        $file = $this->convertDirectorySeparators($file);

        return $this->langDir.DIRECTORY_SEPARATOR.$locale.DIRECTORY_SEPARATOR.$file.'.php';
    }

    /**
     * Get the file path to the fix file
     *
     * @param string $locale
     * @return string
     * @throws BindingResolutionException
     */
    protected function filename(string $locale): string
    {
        if ($this->nameFormat && $this->nameFormat === 'git') {
            return "fixes-{$locale}-".$this->gitBranchName().'.json';
        }
        
        return "fixes-{$locale}-".Carbon::now()->format('Y-m-d').'.json';
    }

    /**
     * Get the name of the current git branch
     *
     * @return string
     * @throws BindingResolutionException
     */
    protected function gitBranchName(): string
    {
        $gitBasePath = base_path().DIRECTORY_SEPARATOR.'.git';
        $branchInfo = file_get_contents($gitBasePath.DIRECTORY_SEPARATOR.'HEAD');

        return rtrim(preg_replace("/(.*?\/){2}/", '', $branchInfo));
    }
}
