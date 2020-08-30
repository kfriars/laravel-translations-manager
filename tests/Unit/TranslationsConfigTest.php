<?php

namespace Kfriars\TranslationsManager\Tests\Unit;

use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Exceptions\TranslationsManagerException;
use Kfriars\TranslationsManager\Tests\TestCase;

class TranslationsConfigTest extends TestCase
{
    /** @test */
    public function it_throws_an_error_when_lang_dir_is_not_configured()
    {
        config(['translations-manager.lang_dir' => null]);

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("You do not have Laravel's 'lang' directory set in the translations-manager config file.");
        
        $this->app->make(ConfigContract::class)->langDir();
    }

    /** @test */
    public function it_throws_an_error_when_storage_dir_is_not_configured()
    {
        config(['translations-manager.storage_dir' => null]);

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("You do not have 'storage_dir' configured for the translations-manager.");
        
        $this->app->make(ConfigContract::class)->storageDir();
    }

    /** @test */
    public function it_throws_an_error_when_fix_name_format_is_not_configured()
    {
        config(['translations-manager.fix_name_format' => null]);

        $this->expectException(TranslationsManagerException::class);
        $this->expectExceptionMessage("You do not have the 'fix_name_format' set in the translations-manager config file.");
        
        $this->app->make(ConfigContract::class)->fixNameFormat();
    }

    /** @test */
    public function it_returns_a_filesystem_for_the_storage_folder()
    {
        $storage = $this->app->make(ConfigContract::class)->storage();

        $this->assertEquals([
            'fixed',
            'fixes',
            'lock'
        ], $storage->directories());
    }

    /** @test */
    public function it_returns_a_filesystem_for_the_lock_folder()
    {
        $lock = $this->app->make(ConfigContract::class)->lock();

        $this->assertEquals([
            'a',
            'c'
        ], $lock->directories());
    }
}
