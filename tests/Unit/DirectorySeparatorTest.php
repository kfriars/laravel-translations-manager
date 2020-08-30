<?php

namespace Kfriars\TranslationsManager\Tests\Unit;

use Kfriars\TranslationsManager\Concerns\HandlesDirectorySeparators;
use Kfriars\TranslationsManager\Tests\TestCase;
use Mockery;

class DirectorySeparatorTest extends TestCase
{
    /** @test */
    public function it_returns_backslashes_for_backslash_directory_separator()
    {
        $mock = Mockery::mock(TestDirectorySeparators::class)
                    ->shouldAllowMockingProtectedMethods()
                    ->makePartial();

        $mock->shouldReceive('isBackslashDirectorySeparator')
            ->andReturn(true);

        $this->assertEquals('a\b\c\d', $mock->convertDirectorySeparators('a/b/c/d'));
    }

    /** @test */
    public function it_returns_forwardslashes_otherwise()
    {
        $mock = Mockery::mock(TestDirectorySeparators::class)
                    ->makePartial()
                    ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('isBackslashDirectorySeparator')
            ->andReturn(false);

        $this->assertEquals('a/b/c/d', $mock->convertDirectorySeparators('a\b\c\d'));
    }
}

class TestDirectorySeparators
{
    use HandlesDirectorySeparators;
}
