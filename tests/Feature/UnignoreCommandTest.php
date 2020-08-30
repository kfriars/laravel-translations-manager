<?php

namespace Kfriars\TranslationsManager\Tests\Feature;

use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Tests\TestCase;

class UnignoreCommandTest extends TestCase
{
    /** @var ConfigContract */
    protected $config;

    protected function makeDependencies(): void
    {
        $this->config = $this->app->make(ConfigContract::class);    
    }

    /** @test */
    public function it_unignores_files_correctly()
    {
        $this->loadScenario($this->app, 'unignores');

        $inFile = include $this->config->ignoresPath();
        $this->assertTrue(isset($inFile['es']['ignored']));

        $this->artisanOutput('translations:unignore es ignored')
             ->assertCommandSuccess()
             ->assertOutputContains('Successfully unignored.');

        $inFile = include $this->config->ignoresPath();
        $this->assertArrayNotHasKey('es', $inFile);
    }

    /** @test */
    public function it_unignores_keys_correctly()
    {
        $this->loadScenario($this->app, 'unignores');

        $inFile = include $this->config->ignoresPath();
        $this->assertTrue(isset($inFile['de']['ignored']['b.c.d']));

        $this->artisanOutput('translations:unignore de ignored b.c.d')
             ->assertCommandSuccess()
             ->assertOutputContains('Successfully unignored.');

        $inFile = include $this->config->ignoresPath();
        $this->assertArrayNotHasKey('de', $inFile);
    }
}
