<?php

namespace Kfriars\TranslationsManager\Tests;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Translation\TranslationServiceProvider;
use Kfriars\TranslationsManager\Contracts\FixerContract;
use Kfriars\TranslationsManager\Contracts\FormatterContract;
use Kfriars\TranslationsManager\Contracts\IgnoresContract;
use Kfriars\TranslationsManager\Entities\ErrorCollection;
use Kfriars\TranslationsManager\Providers\DeferredServicesProvider;
use Kfriars\TranslationsManager\Tests\Traits\TestsCommands;
use Kfriars\TranslationsManager\TranslationsFixesJSONFormatter;
use Kfriars\TranslationsManager\TranslationsManagerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

abstract class TestCase extends Orchestra
{
    use TestsCommands;

    public function setUp(): void
    {
        parent::setUp();

        $this->makeDependencies();
    }

    protected function getPackageProviders($app)
    {
        return [
            TranslationsManagerServiceProvider::class,
            DeferredServicesProvider::class,
        ];
    }

    /**
     * Configure our
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        /** @var \Illuminate\Contracts\Config\Repository */
        $config = $app['config'];

        $config->set('app', [
            'locale' => 'en',
            'locales' => [
                'en' => 'English',
                'fr' => 'French',
                'es' => 'Spanish',
                'de' => 'German',
            ],
        ]);

        $config->set('translations-manager', [
            'storage_dir' => storage_path('translations'),
            'lang_dir' => resource_path('lang'),
            'reference_locale' => 'en',
            'fix_name_format' => 'git',
            'formatter' => TranslationsFixesJSONFormatter::class,
        ]);
        
        $this->loadScenario($app);
    }

    /**
     * Load a test scenario from Fixtures
     *
     * @param string $scenario
     * @return $this
     */
    protected function loadScenario(Application $app, string $scenario = 'default')
    {
        $this->loadLangFixtures($app, $scenario)
             ->loadLockFixtures($app, $scenario)
             ->loadIgnoreFile($app, $scenario.'.php')
             ->loadFixFiles($app, $scenario);


        // Force all contracts to be re-loaded
        if ($scenario !== 'default') {
            $this->reloadDependencies($app);
        }

        return $this;
    }

    /**
     * Resolve any dependencies needed for testing from the application container
     *
     * @return void
     */
    protected function makeDependencies(): void
    {
        // To be overriden
    }

    /**
     * Reload all of the packages dependencis into the application container
     *
     * @param Application $app
     * @return void
     */
    protected function reloadDependencies(Application $app): void
    {
        $app->instance(ArrayFileContract::class, null);
        $app->instance(TranslationsFilesContract::class, null);
        $app->instance(ConfigContract::class, null);
        $app->instance(FormatterContract::class, null);
        $app->instance(LockfilesContract::class, null);
        $app->instance(IgnoresContract::class, null);
        $app->instance(ValidatorContract::class, null);
        $app->instance(FixerContract::class, null);
        $app->instance(ManagerContract::class, null);
        $this->makeDependencies();
    }

    /**
     * For whatever reason this is the only way I can get the translations to update
     * mid-test. A hack I will live with for now.
     */
    protected function resetTranslator(Application $app)
    {
        (new TranslationServiceProvider($app))->register();
        $this->reloadDependencies($app);
    }

    /**
     * Copy lang file Fixtures
     *
     * @param Application $app
     * @param string $folder
     * @return TestCase
     */
    protected function loadLangFixtures(Application $app, string $folder = 'default'): self
    {
        /** @var \Illuminate\Filesystem\Filesystem $filesystem */
        $filesystem = $app['files'];

        $filesystem->cleanDirectory(resource_path('lang'));

        $filesystem->copyDirectory(
            realpath(__DIR__.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$folder),
            resource_path('lang')
        );

        return $this;
    }

    /**
     * Copy lock file Fixtures.
     *
     * @param Application $app
     * @param string $folder
     * @return TestCase
     */
    protected function loadLockFixtures(Application $app, string $folder = 'default'): self
    {
        /** @var \Illuminate\Filesystem\Filesystem $filesystem */
        $filesystem = $app['files'];
        
        $filesystem->cleanDirectory(storage_path('translations'.DIRECTORY_SEPARATOR.'lock'));

        $filesystem->copyDirectory(
            realpath(__DIR__.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'lock'.DIRECTORY_SEPARATOR.$folder),
            storage_path('translations'.DIRECTORY_SEPARATOR.'lock')
        );

        return $this;
    }

    /**
     * Copy ignore file Fixtures.
     *
     * @param Application $app
     * @param null|string $file
     * @return TestCase
     */
    protected function loadIgnoreFile(Application $app, ?string $file = null): self
    {
        /** @var \Illuminate\Filesystem\Filesystem $filesystem */
        $filesystem = $app['files'];
        $filesystem->delete(storage_path('translations'.DIRECTORY_SEPARATOR.'ignores.php'));

        if ($file === null) {
            return $this;
        }

        $fixture = realpath(
            __DIR__.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'ignore'.DIRECTORY_SEPARATOR.$file
        );

        if (! file_exists($fixture)) {
            return $this;
        }
        
        $filesystem->copy($fixture, storage_path('translations'.DIRECTORY_SEPARATOR.$file));
        $filesystem->move(storage_path('translations'.DIRECTORY_SEPARATOR.$file), storage_path('translations'.DIRECTORY_SEPARATOR.'ignores.php'));

        return $this;
    }

    /**
     * Load all fix files required for the scenario
     *
     * @param Application $app
     * @param string $scenario
     * @return TestCase
     */
    protected function loadFixFiles(Application $app, string $scenario): self
    {
        /** @var \Illuminate\Filesystem\Filesystem $filesystem */
        $filesystem = $app['files'];
        $filesystem->cleanDirectory(storage_path('translations'.DIRECTORY_SEPARATOR.'fixes'));
        $filesystem->cleanDirectory(storage_path('translations'.DIRECTORY_SEPARATOR.'fixed'));

        $fix = realpath(
            __DIR__.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'fixes'.DIRECTORY_SEPARATOR.$scenario
        );

        if ($filesystem->exists($fix)) {
            $filesystem->copyDirectory($fix, storage_path('translations'.DIRECTORY_SEPARATOR.'fixes'));
        } else {
            $filesystem->makeDirectory(storage_path('translations'.DIRECTORY_SEPARATOR.'fixes'));
        }

        $fixed = realpath(
            __DIR__.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'fixed'.DIRECTORY_SEPARATOR.$scenario
        );
        
        if ($filesystem->exists($fixed)) {
            $filesystem->copyDirectory($fixed, storage_path('translations'.DIRECTORY_SEPARATOR.'fixed'));
        } else {
            $filesystem->makeDirectory(storage_path('translations'.DIRECTORY_SEPARATOR.'fixed'));
        }

        return $this;
    }

    /**
     * Set the git branch name, by faking the file
     *
     * @param string $name
     * @return TestCase
     */
    protected function setGitBranchName(Application $app, string $name): self
    {
        /** @var \Illuminate\Filesystem\Filesystem $filesystem */
        $filesystem = $app['files'];

        $gitFolder = base_path(".git");

        if (! $filesystem->exists($gitFolder)) {
            $filesystem->makeDirectory($gitFolder);
        }

        $filesystem->put($gitFolder.DIRECTORY_SEPARATOR."HEAD", 'ref: refs/heads/'.$name);

        return $this;
    }

    /**
     * Assert that an error is found in the given array of translations errors
     *
     * @param array $errors
     * @param string $locale
     * @param string $file
     * @param string $key
     * @param string $message
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function assertTranslationsError(
        ErrorCollection $errors,
        string $locale,
        string $file,
        string $key,
        string $message,
        bool $ignored = false
    ) : void {
        $expected = [
            'locale' => $locale,
            'file' => $file,
            'key' => $key,
            'message' => $message,
            'ignored' => $ignored,
        ];

        $this->assertContains($expected, $errors->toArray());
    }
}
