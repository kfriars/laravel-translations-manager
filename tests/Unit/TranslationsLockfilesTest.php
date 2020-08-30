<?php

namespace Kfriars\TranslationsManager\Tests\Unit;

use Kfriars\TranslationsManager\Contracts\ConfigContract;
use Kfriars\TranslationsManager\Contracts\LockfilesContract;
use Kfriars\TranslationsManager\Tests\TestCase;

class TranslationsLockfilesTest extends TestCase
{
    /** @var LockfilesContract */
    protected $lockfiles;
    
    /** @var ConfigContract */
    protected $config;

    protected function makeDependencies(): void
    {
        $this->config = $this->app->make(ConfigContract::class);
        $this->lockfiles = $this->app->make(LockfilesContract::class);
    }

    /** @test */
    public function it_generates_a_new_lockfile_for_ones_that_dont_exist()
    {
        $this->loadScenario($this->app, 'no_lockfiles');

        $file = $this->config->lockDir().DIRECTORY_SEPARATOR.'unlocked.php';
        
        $this->assertFileNotExists($file);

        $this->lockfiles->getLockfile('unlocked');

        $this->assertFileExists($file);

        $locked = include $file;

        $this->assertEquals([
            'unlocked' => 'translation',
            'nested' => [
                'nested' => 'unlocked translations',
            ],
        ], $locked);
    }
}
