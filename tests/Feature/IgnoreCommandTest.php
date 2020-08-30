<?php

namespace Kfriars\TranslationsManager\Tests\Feature;

use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Tests\TestCase;

class IgnoreCommandTest extends TestCase
{
    /** @var ConfigContract */
    protected $config;

    protected function makeDependencies(): void
    {
        $this->config = $this->app->make(ConfigContract::class);    
    }
    
    /** @test */
    public function it_ignores_files_correctly()
    {
        $inFile = include $this->config->ignoresPath();
        $this->assertEmpty($inFile);

        $this->artisanOutput('translations:ignore de a/b/b')
             ->assertCommandSuccess()
             ->assertOutputContains('Successfully ignored.');

        $inFile = include $this->config->ignoresPath();
        $this->assertEquals([
            'de' => [
                'a/b/b' => true,
            ],
        ], $inFile);
    }

    /** @test */
    public function it_ignores_keys_correctly()
    {
        $inFile = include $this->config->ignoresPath();
        $this->assertEmpty($inFile);

        $this->artisanOutput('translations:ignore de a/b/b b.bb.bbb.bbbb')
             ->assertCommandSuccess()
             ->assertOutputContains('Successfully ignored.');

        $inFile = include $this->config->ignoresPath();
        $this->assertEquals([
            'de' => [
                'a/b/b' => [
                    'b.bb.bbb.bbbb' => true,
                ],
            ],
        ], $inFile);
    }
}
