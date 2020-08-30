<?php

namespace Kfriars\TranslationsManager\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Contracts\FixerContract;
use Kfriars\TranslationsManager\Contracts\ManagerContract;
use Kfriars\TranslationsManager\Entities\Error;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;
use Kfriars\TranslationsManager\Tests\TestCase;

class TranslationsFixerTest extends TestCase
{
    /** @var ConfigContract */
    protected $config;
    
    /** @var ManagerContract */
    protected $manager;
    
    /** @var FixerContract */
    protected $fixer;

    protected function makeDependencies(): void
    {
        $this->config = $this->app->make(ConfigContract::class);
        $this->manager = $this->app->make(ManagerContract::class);
        $this->fixer = $this->app->make(FixerContract::class);
    }

    /** @test */
    public function it_writes_fix_files_for_all_errors_in_a_listing()
    {
        /** @var Filesystem */
        $filesystem = $this->config->fixes();

        $this->loadScenario($this->app, 'write_fixes');
        $this->setGitBranchName($this->app, 'write-fixes-test');
        
        $fixes = $filesystem->files();
        $this->assertCount(0, $fixes);

        $listing = $this->manager->listing();
        $this->fixer->generateFixFiles($listing);

        $fixes = $filesystem->files();
        $this->assertCount(3, $fixes);

        $deJson = $filesystem->get('fixes-de-write-fixes-test.json');
        $deFixes = json_decode($deJson, true);

        $this->assertEquals([
            "reference" => "en",
            "locale" => "de",
            "files" => [[
                "file" => "company",
                "translations" => [
                    "departments.finance" => "Finance",
                    "departments.support" => "Support",
                ],
            ],[
                "file" => "email/company/created",
                "translations" => [
                    "body.0" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
                ],
            ], [
                "file" => "email/welcome",
                "translations" => [
                    "subject" => "Welcome to ACME!",
                    "title" => "You have created an account!",
                    "body" => [
                        "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
                        "Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.",
                        "Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.",
                    ],
                ],
            ], [
                "file" => "user",
                "translations" => [
                    "dob" => "Date of Birth",
                    "phone" => [
                        "number" => "Phone Number",
                        "extension" => "Extension",
                    ],
                    "email" => "Updated",
                ],
            ]],
        ], $deFixes);
        
        $esJson = $filesystem->get('fixes-es-write-fixes-test.json');
        $esFixes = json_decode($esJson, true);

        $this->assertEquals([
            "reference" => "en",
            "locale" => "es",
            "files" => [[
                "file" => "email/company/created",
                "translations" => [
                    "title" => ":name Created!",
                    "subject" => "You have created a new company :name!",
                    "body" => [
                        "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
                        "Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.",
                        "Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.",
                    ],
                ],
            ], [
                "file" => "email/invite",
                "translations" => [
                    "subject" => "You have been invited to join ACME!",
                    "title" => "Join Acme Now!",
                    "body" => [
                        "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
                        "Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.",
                        "Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.",
                    ],
                ],
            ], [
                "file" => "email/welcome",
                "translations" => [
                    "subject" => "Welcome to ACME!",
                    "title" => "You have created an account!",
                    "body" => [
                        "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
                        "Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.",
                        "Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.",
                    ],
                ],
            ], [
                "file" => "user",
                "translations" => [
                    "email" => "Updated",
                ],
            ]],
        ], $esFixes);

        $frJson = $filesystem->get('fixes-fr-write-fixes-test.json');
        $frFixes = json_decode($frJson, true);

        $this->assertEquals([
            "reference" => "en",
            "locale" => "fr",
            "files" => [[
                "file" => "email/company/created",
                "translations" => [
                    "body.0" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
                ],
            ],[
                "file" => "email/invite",
                "translations" => [
                    "subject" => "You have been invited to join ACME!",
                ],
            ], [
                "file" => "time",
                "translations" => [
                    "year" => "Year",
                    "second" => "Second",
                ],
            ], [
                "file" => "user",
                "translations" => [
                    "name" => [
                        "first" => "First Name",
                        "last" => "Last Name",
                    ],
                    "phone.number" => "Phone Number",
                    "email" => "Updated",
                ],
            ]],
        ], $frFixes);
    }

    /** @test */
    public function it_aborts_writing_fix_files_when_reference_translation_files_are_missing()
    {
        $this->loadScenario($this->app, 'write_fixes');
        $this->setGitBranchName($this->app, 'write-fixes-test');

        $listing = $this->manager->listing();

        $listing->locales()->where('code', 'de')->first()
                ->files()->where('path', 'user')->first()
                ->addError(new Error(
                    'de',
                    'not/in/en',
                    'german.key',
                    Error::REFERENCE_FILE_MISSING,
                    false
                ));

        $listing->locales()->where('code', 'fr')->first()
                ->files()->where('path', 'time')->first()
                ->addError(new Error(
                    'fr',
                    'not/in/en',
                    'french.key',
                    Error::REFERENCE_FILE_MISSING,
                    false
                ));
        
        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage(
            "You are attempting to create fix file(s) for 'de/not/in/en', 'fr/not/in/en' but they are not in the reference locale."
        );

        $this->fixer->generateFixFiles($listing);
    }

    /** @test */
    public function it_can_write_fix_files_with_date_as_name_format()
    {
        Carbon::setTestNow('2020-08-29');

        config(['translations-manager.fix_name_format' => 'date']);

        /** @var Filesystem */
        $filesystem = $this->config->fixes();

        $this->loadScenario($this->app, 'write_fixes');
        
        $fixes = $filesystem->files();
        $this->assertCount(0, $fixes);

        $listing = $this->manager->listing();
        $this->fixer->generateFixFiles($listing);

        $fixes = $filesystem->files();

        $this->assertEquals('fixes-de-2020-08-29.json', $fixes[0]);
        $this->assertEquals('fixes-es-2020-08-29.json', $fixes[1]);
        $this->assertEquals('fixes-fr-2020-08-29.json', $fixes[2]);
    }

    /** @test */
    public function it_aborts_fixing_files_when_the_reference_locale_is_specified()
    {
        $this->loadFixFiles($this->app, 'wrong_name_format');

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("You cannot fix the reference locale 'en'");

        $this->fixer->fix('en');
    }

    /** @test */
    public function it_aborts_fixing_files_when_unsupported_locales_are_specified()
    {
        $this->loadFixFiles($this->app, 'wrong_name_format');

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("You cannot fix the locale(s) 'uk' as they are not supported locale(s).");

        $this->fixer->fix('uk');
    }

    /** @test */
    public function it_aborts_fixing_files_when_fix_files_are_improperly_named()
    {
        $this->loadFixFiles($this->app, 'wrong_name_format');

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("The fix file 'i-violate-format.json' is not in the correct format 'fixes-{locale}-{date|git_branch}.json'");

        $this->fixer->fix('de');
    }

    /** @test */
    public function it_aborts_fixing_files_when_more_than_one_fix_file_is_present_for_a_locale()
    {
        $this->loadFixFiles($this->app, 'duplicate_fixes');

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("You have multiple fix files for the locale 'de' in the directory '".storage_path('translations'.DIRECTORY_SEPARATOR.'fixed')."'.");

        $this->fixer->fix('de');
    }

    /** @test */
    public function it_aborts_fixing_files_if_a_specified_locale_is_missing_a_fix_file()
    {
        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("No fix files were found in '".storage_path('translations'.DIRECTORY_SEPARATOR.'fixed')."' for the following locales 'de', 'fr'.");

        $this->fixer->fixMany(['de', 'fr']);
    }

    /** @test */
    public function it_aborts_fixing_files_if_the_fix_files_do_not_contain_well_formed_json()
    {
        $this->loadFixFiles($this->app, 'poorly_formed');
        $this->setGitBranchName($this->app, 'fix-files-test');

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("The file 'fixes-de-fix-files-test.json' does not contain well formed JSON.");

        $this->fixer->fix('de');
    }

    /** @test */
    public function it_aborts_fixing_files_if_reference_files_are_missing()
    {
        $this->loadFixFiles($this->app, 'missing_reference_files');
        $this->setGitBranchName($this->app, 'fix-files-test');

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("There is no translation file 'i/dont/exist' in the reference language.");

        $this->fixer->fix('es');
    }

    /** @test */
    public function it_aborts_fixing_files_if_reference_translations_are_missing()
    {
        $this->loadFixFiles($this->app, 'missing_reference_keys');
        $this->setGitBranchName($this->app, 'fix-files-test');

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("In the file 'fr/a/b/b' the keys 'a', 'b.a' could not be found in the reference locale.");

        $this->fixer->fix('fr');
    }

    /** @test */
    public function it_aborts_fixing_files_if_reference_translations_have_type_mismatch()
    {
        $this->loadFixFiles($this->app, 'reference_key_type_mismatch');
        $this->setGitBranchName($this->app, 'fix-files-test');

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("In the file 'fr/a/b/b' the keys 'b.bb.bbb.bbbb.uh_oh' could not be found in the reference locale.");

        $this->fixer->fix('fr');
    }

    /** @test */
    public function it_fixes_all_translations_in_a_locales_fix_file()
    {
        $this->loadScenario($this->app, 'use_fixes');
        $this->setGitBranchName($this->app, 'use-fixes-test');

        // Assert the number of errors in the status
        $listing = $this->manager->listing(['de']);

        $this->assertEquals(9, $listing->errors()->count());
        $this->assertEquals(6, $listing->critical()->count());

        $this->fixer->fix('de');
        
        $this->resetTranslator($this->app);

        $listing = $this->manager->listing(['de']);

        $this->assertEquals(1, $listing->errors()->count());
        $this->assertEquals(0, $listing->critical()->count());
    }
}
