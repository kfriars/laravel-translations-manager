<?php

namespace Kfriars\TranslationsManager\Tests\Feature;

use Kfriars\TranslationsManager\Tests\TestCase;

class ValidateCommandTest extends TestCase
{
    /** @test */
    public function it_outputs_passed_validation_correctly()
    {
        $this->artisanOutput('translations:validate')
            ->assertCommandSuccess()
            ->assertOutputContains('Validation Passed');
    }

    /** @test */
    public function it_outputs_failed_validation_correctly()
    {
        $this->loadScenario($this->app, 'missing_strings');

        $this->artisanOutput('translations:validate')
            ->assertCommandFailure()
            ->assertOutputContains('Validation Failed');
    }

    /** @test */
    public function it_outputs_error_messages_to_the_console()
    {
        $this->artisanOutput('translations:validate error')
            ->assertCommandFailure()
            ->assertOutputContains("You do not have a locale folder for the given locale(s) 'error'.");
    }
}
