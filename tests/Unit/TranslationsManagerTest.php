<?php

namespace Kfriars\TranslationsManager\Tests\Unit;

use Kfriars\TranslationsManager\Contracts\ManagerContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;
use Kfriars\TranslationsManager\Tests\TestCase;
use ReflectionObject;

class TranslationsManagerTest extends TestCase
{
    /** @var ManagerContract */
    protected $manager;

    protected function makeDependencies(): void
    {
        $this->manager = $this->app->make(ManagerContract::class);
    }
    
    /** @test */
    public function it_throws_an_error_when_the_reference_language_is_not_set()
    {
        config(['translations-manager.reference_locale' => null]);

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("You must set the reference_locale in your translations-manager config file.");

        app()->instance(ManagerContract::class, null); // Force singleton to be re-loaded with new config
    }

    /** @test */
    public function it_throws_an_error_when_the_reference_language_is_set_to_a_non_existent_locale()
    {
        config(['translations-manager.reference_locale' => 'en_CA']);

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("You do not have a folder for the reference locale 'en_CA'");

        app()->instance(ManagerContract::class, null); // Force singleton to be re-loaded with new config
    }

    /** @test */
    public function it_throws_an_error_when_a_supported_language_has_non_existent_locales()
    {
        config(['translations-manager.supported_locales' => ['en_CA', 'fr_CA']]);

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("You do not have a locale folder for the supported locales 'en_CA, fr_CA' in your config file");

        app()->instance(ManagerContract::class, null); // Force singleton to be re-loaded with new config
    }

    /** @test */
    public function it_supports_only_configured_locales()
    {
        config(['translations-manager.supported_locales' => ['de', 'fr']]);

        app()->instance(ManagerContract::class, null); // Force singleton to be re-loaded with new config
        $this->manager = $this->app->make(ManagerContract::class);
        
        
        $reflection = new ReflectionObject($this->manager);
        $supported = $reflection->getProperty('supportedLocales');
        $supported->setAccessible(true);

        $this->assertEquals(['de', 'fr'], $supported->getValue($this->manager));
    }

    /** @test */
    public function it_will_not_list_the_status_of_unsupported_locales()
    {
        $this->loadScenario($this->app, 'ignores_errors');

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("You do not have a locale folder for the given locale(s) 'i, dont, exist'.");

        $this->manager->listing(['de', 'es', 'fr', 'i', 'dont', 'exist']);
    }
    
    /** @test */
    public function it_will_not_validate_the_reference_locale()
    {
        $this->loadScenario($this->app, 'ignores_errors');

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("You can not use the translations manager with the reference locale 'en'");

        $this->manager->listing(['de', 'es', 'en']);
    }

    /** @test */
    public function it_can_list_the_status_of_supported_locales()
    {
        $this->loadScenario($this->app, 'ignores_errors');
        $status = $this->manager->listing(['de', 'es', 'fr'])->toArray();

        $this->assertEquals([
            "locale" => "de",
            "files" => [[
                "file" => "common",
                "ignored" => true,
                "errors" => [[
                    "locale" => "de",
                    "file" => "common",
                    "key" => "last_name",
                    "message" => "translation_missing",
                    "ignored" => true,
                ], [
                    "locale" => "de",
                    "file" => "common",
                    "key" => "status",
                    "message" => "translation_missing",
                    "ignored" => true,
                ]],
            ], [
                "file" => "mail/auth/password_reset",
                "ignored" => true,
                "errors" => [[
                    "locale" => "de",
                    "file" => "mail/auth/password_reset",
                    "key" => "FILE_ERROR",
                    "message" => "file_not_translated",
                    "ignored" => true,
                ]],
            ], [
                "file" => "mail/auth/registration",
                "ignored" => false,
                "errors" => [[
                    "locale" => "de",
                    "file" => "mail/auth/registration",
                    "key" => "extra",
                    "message" => "no_reference_translation",
                    "ignored" => true,
                ], [
                    "locale" => "de",
                    "file" => "mail/auth/registration",
                    "key" => "body",
                    "message" => "incorrect_translation_type",
                    "ignored" => true,
                ]],
            ], [
                "file" => "mail/leads/newsletter",
                "ignored" => false,
                "errors" => [[
                    "locale" => "de",
                    "file" => "mail/leads/newsletter",
                    "key" => "title",
                    "message" => "reference_translation_updated",
                    "ignored" => true,
                ]],
            ]],
        ], $status['locales'][0]);

        $this->assertEquals([
            "locale" => "es",
            "files" => [[
                "file" => "common",
                "ignored" => false,
                "errors" => [[
                    "locale" => "es",
                    "file" => "common",
                    "key" => "extra",
                    "message" => "no_reference_translation",
                    "ignored" => true,
                ], [
                    "locale" => "es",
                    "file" => "common",
                    "key" => "status.ooo",
                    "message" => "translation_missing",
                    "ignored" => true,
                ]],
            ], [
                "file" => "mail/auth/password_reset",
                "ignored" => false,
                "errors" => [[
                    "locale" => "es",
                    "file" => "mail/auth/password_reset",
                    "key" => "title",
                    "message" => "translation_missing",
                    "ignored" => true,
                ]],
            ], [
                "file" => "mail/auth/registration",
                "ignored" => true,
                "errors" => [[
                    "locale" => "es",
                    "file" => "mail/auth/registration",
                    "key" => "subject",
                    "message" => "incorrect_translation_type",
                    "ignored" => true,
                ]],
            ], [
                "file" => "mail/leads/newsletter",
                "ignored" => true,
                "errors" => [[
                    "locale" => "es",
                    "file" => "mail/leads/newsletter",
                    "key" => "FILE_ERROR",
                    "message" => "file_not_translated",
                    "ignored" => true,
                ]],
            ]],
        ], $status['locales'][1]);

        $this->assertEquals([
            "locale" => "fr",
            "files" => [[
                "file" => "common",
                "ignored" => true,
                "errors" => [[
                    "locale" => "fr",
                    "file" => "common",
                    "key" => "FILE_ERROR",
                    "message" => "file_not_translated",
                    "ignored" => true,
                ]],
            ], [
                "file" => "mail/auth/password_reset",
                "ignored" => true,
                "errors" => [[
                    "locale" => "fr",
                    "file" => "mail/auth/password_reset",
                    "key" => "subject",
                    "message" => "translation_missing",
                    "ignored" => true,
                ]],
            ], [
                "file" => "mail/auth/registration",
                "ignored" => false,
                "errors" => [[
                    "locale" => "fr",
                    "file" => "mail/auth/registration",
                    "key" => "title",
                    "message" => "incorrect_translation_type",
                    "ignored" => true,
                ]],
            ], [
                "file" => "mail/leads/newsletter",
                "ignored" => false,
                "errors" => [[
                    "locale" => "fr",
                    "file" => "mail/leads/newsletter",
                    "key" => "title",
                    "message" => "reference_translation_updated",
                    "ignored" => true,
                ],[
                    "locale" => "fr",
                    "file" => "mail/leads/newsletter",
                    "key" => "extra",
                    "message" => "no_reference_translation",
                    "ignored" => true,
                ]],
            ]],
        ], $status['locales'][2]);
    }

    /** @test */
    public function it_can_ignore_any_error_in_any_file()
    {
        $this->loadScenario($this->app, 'ignores_errors');

        $errors = $this->manager->errors(['de', 'es', 'fr'], false);
        $this->assertCount(16, $errors);

        $errors = $this->manager->errors(['de', 'es', 'fr'], true);
        $this->assertCount(0, $errors);

        $this->assertTrue($this->manager->hasErrors(['de', 'es', 'fr'], false));
        $this->assertFalse($this->manager->hasErrors(['de', 'es', 'fr'], true));
    }
}
