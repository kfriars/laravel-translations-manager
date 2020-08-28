<?php

namespace Kfriars\TranslationsManager\Tests\Feature;

use Kfriars\TranslationsManager\Tests\TestCase;
use Symfony\Component\Console\Exception\RuntimeException;

class FixCommandTest extends TestCase
{
    /** @test */
    public function it_requires_locales_to_be_specified()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "locales")');
        $this->artisanOutput('translations:fix');
    }

    /** @test */
    public function it_fixes_files_successfully()
    {
        $this->loadScenario($this->app, 'use_fixes');

        $this->artisanOutput('translations:fix es')
             ->assertCommandSuccess()
             ->assertOutputContains("The locale(s) 'es' have been fixed.");
    }

    /** @test */
    public function it_handles_translations_manager_exceptions()
    {
        $this->loadScenario($this->app, 'use_fixes');

        $this->artisanOutput('translations:fix ar')
             ->assertCommandFailure()
             ->assertOutputContains("You cannot fix the locale(s) 'ar' as they are not supported locale(s).");
    }
}
