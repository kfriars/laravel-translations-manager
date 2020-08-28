<?php

namespace Kfriars\TranslationsManager\Tests\Unit;

use Kfriars\TranslationsManager\Contracts\FileReaderContract;
use Kfriars\TranslationsManager\Contracts\ValidatorContract;
use Kfriars\TranslationsManager\Tests\TestCase;
use ReflectionObject;

class TranslationsValidatorTest extends TestCase
{
    /** @var FileReaderContract */
    protected $files;

    /** @var ValidatorContract */
    protected $validator;

    protected function makeDependencies(): void
    {
        $this->files = $this->app->make(FileReaderContract::class);
        $this->validator = $this->app->make(ValidatorContract::class);
    }

    /** @test */
    public function it_validates_valid_translations_without_errors()
    {
        $listing = $this->files->listLocale('en');
        $errors = $this->validator->validate('en', $listing, ['de', 'es', 'fr'])->errors();

        $this->assertCount(0, $errors);
    }

    /** @test */
    public function it_finds_missing_translations_files()
    {
        $this->loadScenario($this->app, 'missing_files');

        $listing = $this->files->listLocale('en');
        $errors = $this->validator->validate('en', $listing, ['de', 'es', 'fr'])->errors();

        $this->assertTranslationsError($errors, 'de', 'german', 'FILE_ERROR', 'file_not_translated');
        $this->assertTranslationsError($errors, 'es', 'spanish', 'FILE_ERROR', 'file_not_translated');
        $this->assertTranslationsError($errors, 'fr', 'french', 'FILE_ERROR', 'file_not_translated');
    }

    /** @test */
    public function it_finds_missing_translation_strings()
    {
        $this->loadScenario($this->app, 'missing_strings');

        $listing = $this->files->listLocale('en');
        $errors = $this->validator->validate('en', $listing, ['de', 'es', 'fr'])->errors();

        $this->assertTranslationsError($errors, 'de', 'missing_keys', 'de.missing', 'translation_missing');
        $this->assertTranslationsError($errors, 'de', 'nested/missing_keys', 'de.missing', 'translation_missing');

        $this->assertTranslationsError($errors, 'es', 'missing_keys', 'de.fr.es.missing', 'translation_missing');
        $this->assertTranslationsError($errors, 'es', 'nested/missing_keys', 'es.missing', 'translation_missing');

        $this->assertTranslationsError($errors, 'fr', 'missing_keys', 'de.fr.missing', 'translation_missing');
        $this->assertTranslationsError($errors, 'fr', 'nested/missing_keys', 'fr.missing', 'translation_missing');
    }

    /** @test */
    public function it_finds_changed_reference_translations()
    {
        $this->loadScenario($this->app, 'changed_reference');

        $listing = $this->files->listLocale('en');
        $errors = $this->validator->validate('en', $listing, ['de', 'es', 'fr'])->errors();

        $this->assertTranslationsError($errors, 'de', 'translations', 'changed', 'reference_translation_updated');
        $this->assertTranslationsError($errors, 'es', 'translations', 'changed', 'reference_translation_updated');
        $this->assertTranslationsError($errors, 'fr', 'translations', 'changed', 'reference_translation_updated');
    }

    /** @test */
    public function it_finds_type_mismatches_in_translations()
    {
        $this->loadScenario($this->app, 'type_mismatches');

        $listing = $this->files->listLocale('en');
        $errors = $this->validator->validate('en', $listing, ['de', 'es', 'fr'])->errors();

        $this->assertTranslationsError($errors, 'de', 'translations', 'im_an_array', 'incorrect_translation_type');
        $this->assertTranslationsError($errors, 'de', 'translations', 'im_a_string', 'incorrect_translation_type');

        $this->assertTranslationsError($errors, 'es', 'translations', 'im_an_array', 'incorrect_translation_type');
        $this->assertTranslationsError($errors, 'es', 'translations', 'im_a_string', 'incorrect_translation_type');

        $this->assertTranslationsError($errors, 'fr', 'translations', 'im_an_array', 'incorrect_translation_type');
        $this->assertTranslationsError($errors, 'fr', 'translations', 'im_a_string', 'incorrect_translation_type');
    }

    /** @test */
    public function it_finds_translations_that_do_not_have_a_reference()
    {
        $this->loadScenario($this->app, 'no_reference');

        $listing = $this->files->listLocale('en');
        $errors = $this->validator->validate('en', $listing, ['de', 'es', 'fr'])->errors();

        $this->assertTranslationsError($errors, 'de', 'translations', 'german_translation', 'no_reference_translation');
        $this->assertTranslationsError($errors, 'es', 'translations', 'spanish_translation', 'no_reference_translation');
        $this->assertTranslationsError($errors, 'fr', 'translations', 'french_translation', 'no_reference_translation');
    }

    /** @test */
    public function it_finds_errors_with_bad_reference_locales()
    {
        $reflection = new ReflectionObject($this->validator);
        $method = $reflection->getMethod('validateTranslations');
        $method->setAccessible(true);

        $errors = $method->invokeArgs($this->validator, [ 'd', 'bad_locale', 'de' ]);

        $this->assertEquals([
            'locale' => 'de',
            'file' => 'd',
            'key' => 'FILE_ERROR',
            'message' => 'reference_file_missing',
            'ignored' => false,
        ], $errors[0]->toArray());
    }
}
