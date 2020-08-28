<?php

namespace Kfriars\TranslationsManager\Tests\Feature;

use Kfriars\TranslationsManager\Contracts\ManagerContract;
use Kfriars\TranslationsManager\Tests\TestCase;

class CleanCommandTest extends TestCase
{
    /** @var ManagerContract */
    protected $manager;

    protected function makeDependencies(): void
    {
        $this->manager = $this->app->make(ManagerContract::class);
    }

    /** @test */
    public function it_outputs_the_clean_command_correctly()
    {
        $this->loadScenario($this->app, 'no_reference');

        $listing = $this->manager->listing();
        $this->assertCount(4, $listing->errors());

        $this->artisanOutput('translations:clean')
            ->assertCommandSuccess()
            ->assertOutputContains("There were 4 error(s) dead translations cleaned from the supported locales");

        $this->resetTranslator($this->app);

        $listing = $this->manager->listing();
        $this->assertCount(0, $listing->errors());
    }

    /** @test */
    public function it_writes_translations_manager_exceptions_as_error_output()
    {
        $this->artisanOutput('translations:clean en')
            ->assertCommandFailure()
            ->assertOutputContains("You can not use the translations manager with the reference locale 'en'");
    }
}
