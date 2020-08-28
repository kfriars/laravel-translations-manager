<?php

namespace Kfriars\TranslationsManager\Tests\Feature;

use Kfriars\TranslationsManager\Tests\TestCase;

class GenerateFixesCommandTest extends TestCase
{
    /** @test */
    public function it_generates_fix_files_successfully()
    {
        $this->loadScenario($this->app, 'write_fixes');

        $this->artisanOutput('translations:generate-fixes fr')
             ->assertCommandSuccess()
             ->assertOutputContains("Fix files have been generated for 'fr'.");
    }

    /** @test */
    public function it_handles_translations_manager__exceptions()
    {
        $this->loadScenario($this->app, 'use_fixes');

        $this->artisanOutput('translations:generate-fixes ar')
             ->assertCommandFailure()
             ->assertOutputContains("You do not have a locale folder for the given locale(s) 'ar'.");
    }
}
