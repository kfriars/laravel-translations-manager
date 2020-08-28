<?php

namespace Kfriars\TranslationsManager\Tests\Feature;

use Kfriars\TranslationsManager\Tests\TestCase;

class IgnoreCommandTest extends TestCase
{
    /** @test */
    public function it_ignores_files_correctly()
    {
        $inFile = include config('translations-manager.ignores');
        $this->assertEmpty($inFile);

        $this->artisanOutput('translations:ignore de a/b/b')
             ->assertCommandSuccess()
             ->assertOutputContains('Successfully ignored.');

        $inFile = include config('translations-manager.ignores');
        $this->assertEquals([
            'de' => [
                'a/b/b' => true,
            ],
        ], $inFile);
    }

    /** @test */
    public function it_ignores_keys_correctly()
    {
        $inFile = include config('translations-manager.ignores');
        $this->assertEmpty($inFile);

        $this->artisanOutput('translations:ignore de a/b/b b.bb.bbb.bbbb')
             ->assertCommandSuccess()
             ->assertOutputContains('Successfully ignored.');

        $inFile = include config('translations-manager.ignores');
        $this->assertEquals([
            'de' => [
                'a/b/b' => [
                    'b.bb.bbb.bbbb' => true,
                ],
            ],
        ], $inFile);
    }
}
