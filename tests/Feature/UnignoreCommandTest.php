<?php

namespace Kfriars\TranslationsManager\Tests\Feature;

use Kfriars\TranslationsManager\Tests\TestCase;

class UnignoreCommandTest extends TestCase
{
    /** @test */
    public function it_unignores_files_correctly()
    {
        $this->loadScenario($this->app, 'unignores');

        $inFile = include config('translations-manager.ignores');
        $this->assertTrue(isset($inFile['es']['ignored']));

        $this->artisanOutput('translations:unignore es ignored')
             ->assertCommandSuccess()
             ->assertOutputContains('Successfully unignored.');

        $inFile = include config('translations-manager.ignores');
        $this->assertArrayNotHasKey('es', $inFile);
    }

    /** @test */
    public function it_unignores_keys_correctly()
    {
        $this->loadScenario($this->app, 'unignores');

        $inFile = include config('translations-manager.ignores');
        $this->assertTrue(isset($inFile['de']['ignored']['b.c.d']));

        $this->artisanOutput('translations:unignore de ignored b.c.d')
             ->assertCommandSuccess()
             ->assertOutputContains('Successfully unignored.');

        $inFile = include config('translations-manager.ignores');
        $this->assertArrayNotHasKey('de', $inFile);
    }
}
