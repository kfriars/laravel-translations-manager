<?php

namespace Kfriars\TranslationsManager\Tests\Unit;

use Kfriars\TranslationsManager\Contracts\FileReaderContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;
use Kfriars\TranslationsManager\Tests\TestCase;
use LogicException;
use ReflectionObject;

class TranslationsFileReaderTest extends TestCase
{
    /** @var FileReaderContract */
    protected $files;

    protected function makeDependencies(): void
    {
        $this->files = $this->app->make(FileReaderContract::class);
    }

    /** @test */
    public function it_correctly_lists_the_lang_folders()
    {
        $locales = $this->files->localeFolders();

        $this->assertEquals(['de', 'en', 'es', 'fr'], $locales);
    }

    /** @test */
    public function it_lists_a_directory_in_the_desired_format()
    {
        $listing = $this->files->listLocale('en');

        $this->assertEquals([
            'a/a',
            'a/b/b',
            'c/c',
            'd',
        ], $listing);
    }

    /** @test */
    public function it_lists_a_sub_directory_in_the_desired_format()
    {
        $listing = $this->files->listLocale('en', '.'.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'b');

        $this->assertEquals([
            'a/b/b',
        ], $listing);
    }

    /** @test */
    public function it_allows_subfolder_in_dot_and_no_dot_formats()
    {
        $withDot = $this->files->listLocale('en', '.'.DIRECTORY_SEPARATOR.'a');
        $noDot = $this->files->listLocale('en', 'a');

        $this->assertEquals(['a/a', 'a/b/b'], $withDot);
        $this->assertEquals(['a/a', 'a/b/b'], $noDot);
        $this->assertEquals($withDot, $noDot);
    }

    /** @test */
    public function it_handles_trailing_slashes()
    {
        $listing = $this->files->listLocale('en', 'a'.DIRECTORY_SEPARATOR.'b'.DIRECTORY_SEPARATOR);

        $this->assertEquals(['a/b/b'], $listing);
        $this->assertCount(1, $listing);
    }

    /** @test */
    public function it_does_not_allow_subfolders_with_absolute_path()
    {
        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage('You can only reference translations folders using a relative path.');

        $this->files->listLocale('en', DIRECTORY_SEPARATOR.'a');
    }

    /** @test */
    public function it_handles_subfolders_that_do_not_exist()
    {
        $directory = 'i_dont_exist';
        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("The translations directory '{$directory}' does not exist");
        
        $this->files->listLocale('en', 'i_dont_exist');
    }

    /** @test */
    public function it_will_not_allow_parent_directory_scanning()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Path is outside of the defined root');
        $this->files->listLocale('en', '.'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'storage');
    }

    /** @test */
    public function it_throws_an_error_when_getting_a_filesystem_for_a_non_existent_locale()
    {
        $reflection = new ReflectionObject($this->files);
        $method = $reflection->getMethod('getLocaleFilesystem');
        $method->setAccessible(true);

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("The translations folder 'bad_locale' does not exist");
        
        $method->invokeArgs($this->files, [ 'bad_locale' ]);
    }
}
