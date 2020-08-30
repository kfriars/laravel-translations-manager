<?php

namespace Kfriars\TranslationsManager\Tests\Unit;

use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Contracts\IgnoresContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;
use Kfriars\TranslationsManager\Tests\TestCase;

class TranslationsIgnoresTest extends TestCase
{
    /** @var IgnoresContract */
    protected $ignores;
    
    /** @var ConfigContract */
    protected $config;

    protected function makeDependencies(): void
    {
        $this->config = $this->app->make(ConfigContract::class);
        $this->ignores = $this->app->make(IgnoresContract::class);
    }

    /** @test */
    public function it_creates_a_new_ignores_file_if_it_does_not_exist()
    {
        $file = $this->config->ignoresPath();

        /** @var \Illuminate\Filesystem\Filesystem $filesystem */
        $filesystem = $this->app['files'];
        $filesystem->delete($file);

        $this->assertFileNotExists($file);

        $this->loadIgnoreFile($this->app, null);
        $this->app->instance(IgnoresContract::class, null);

        $this->assertFileExists($file);

        $locked = include $file;

        $this->assertEquals([], $locked);
    }

    /** @test */
    public function it_can_add_ignores_for_an_entire_file()
    {
        $this->assertEmpty($this->ignores->all());
        $this->ignores->ignore('de', 'a/b/b');
        
        $all = $this->ignores->all();
        $this->assertEquals([
            'de' => [
                'a/b/b' => true,
            ],
        ], $all);
        
        $inFile = include $this->config->ignoresPath();
        $this->assertEquals($all, $inFile);
    }

    /** @test */
    public function it_can_add_ignores_for_a_specific_key()
    {
        $this->assertEmpty($this->ignores->all());
        $this->ignores->ignore('de', 'a/a', 'a.aa.aaa.aaaa');
        
        $all = $this->ignores->all();
        $this->assertEquals([
            'de' => [
                'a/a' => [
                    'a.aa.aaa.aaaa' => true,
                ],
            ],
        ], $all);
        
        $inFile = include $this->config->ignoresPath();
        $this->assertEquals($all, $inFile);
    }

    /** @test */
    public function if_a_file_is_already_ignored_it_will_not_ignore_keys()
    {
        $this->assertEmpty($this->ignores->all());
        $this->ignores->ignore('de', 'a/a');
        $this->ignores->ignore('de', 'a/a', 'a.aa.aaa.aaaa');

        $all = $this->ignores->all();

        $this->assertEquals([
            'de' => [
                'a/a' => true,
            ],
        ], $all);
        
        $inFile = include $this->config->ignoresPath();
        $this->assertEquals($all, $inFile);
    }

    /** @test */
    public function if_a_key_is_already_ignored_the_file_can_still_be_ignored()
    {
        $this->assertEmpty($this->ignores->all());
        $this->ignores->ignore('de', 'c/c', 'c.cc.ccc.cccc');
        $this->ignores->ignore('de', 'c/c');
        
        $all = $this->ignores->all();

        $this->assertEquals([
            'de' => [
                'c/c' => true,
            ],
        ], $all);
        
        $inFile = include $this->config->ignoresPath();
        $this->assertEquals($all, $inFile);
    }

    /** @test */
    public function if_a_file_is_already_ignored_nothing_happens_when_ignoring_it_again()
    {
        $this->assertEmpty($this->ignores->all());
        $this->ignores->ignore('de', 'c/c');
        $firstIgnore = $this->ignores->all();

        $this->ignores->ignore('de', 'c/c');
        $secondIgnore = $this->ignores->all();

        $this->assertEquals($firstIgnore, $secondIgnore);
    }

    /** @test */
    public function if_a_key_is_already_ignored_nothing_happens_when_ignoring_it_again()
    {
        $this->assertEmpty($this->ignores->all());
        $this->ignores->ignore('de', 'c/c', 'c.cc.ccc.cccc');
        $firstIgnore = $this->ignores->all();

        $this->ignores->ignore('de', 'c/c', 'c.cc.ccc.cccc');
        $secondIgnore = $this->ignores->all();

        $this->assertEquals($firstIgnore, $secondIgnore);
    }

    /** @test */
    public function it_can_unignore_a_key()
    {
        $this->assertEmpty($this->ignores->all());
        
        $this->ignores->ignore('de', 'c/c', 'c.cc.ccc.cccc');
        $this->assertCount(1, $this->ignores->all());

        $this->ignores->unignore('de', 'c/c', 'c.cc.ccc.cccc');
        
        $this->assertEmpty($this->ignores->all());
    }

    /** @test */
    public function it_can_unignore_a_file()
    {
        $this->assertEmpty($this->ignores->all());
        
        $this->ignores->ignore('de', 'd');
        $this->assertCount(1, $this->ignores->all());

        $this->ignores->unignore('de', 'd');
        
        $this->assertEmpty($this->ignores->all());
    }

    /** @test */
    public function nothing_happens_when_a_non_existent_key_is_unignored()
    {
        $this->assertEmpty($this->ignores->all());
        
        $this->ignores->ignore('de', 'd');
        $this->assertCount(1, $this->ignores->all());

        $this->ignores->unignore('de', 'i-dont-exist');
        
        $this->assertCount(1, $this->ignores->all());
    }
}
