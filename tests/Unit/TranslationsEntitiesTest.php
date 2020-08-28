<?php

namespace Kfriars\TranslationsManager\Tests\Unit;

use Kfriars\TranslationsManager\Entities\Error;
use Kfriars\TranslationsManager\Entities\Listing;
use Kfriars\TranslationsManager\Entities\Locale;
use Kfriars\TranslationsManager\Entities\TranslationsFile;
use Kfriars\TranslationsManager\Tests\TestCase;

class TranslationsEntitiesTest extends TestCase
{
    /** @var Listing */
    private $listing;

    /** @var Locale */
    private $locale;

    /** @var TranslationsFile */
    private $file;

    /** @var Error */
    private $critical;

    /** @var Error */
    private $noncritical;

    protected function makeDependencies(): void
    {
        $this->file = new TranslationsFile('path/to/file', false);
        $this->critical = new Error('de', 'path/to/file', 'some.key', Error::TRANSLATION_MISSING, true);
        $this->noncritical = new Error('de', 'path/to/file', 'some.key', Error::REFERENCE_TRANSLATION_UPDATED, false);
        
        $this->file->addError($this->critical);
        $this->file->addError($this->noncritical);

        $this->locale = new Locale('de');
        $this->locale->addFile($this->file);

        $this->listing = new Listing('en');
        $this->listing->addLocale($this->locale);
    }

    /** @test */
    public function errors_have_array_access()
    {
        $this->assertEquals('some.key', $this->critical['key']);
        
        $this->critical['key'] = 'updated.key';
        $this->assertEquals('updated.key', $this->critical['key']);

        unset($this->critical['key']);
        $this->assertNull($this->critical['key']);
    }
    
    /** @test */
    public function files_have_array_access()
    {
        $this->assertEquals('path/to/file', $this->file['path']);
        
        $this->file['path'] = 'updated/path';
        $this->assertEquals('updated/path', $this->file['path']);

        unset($this->file['path']);
        $this->assertNull($this->file['path']);
    }

    /** @test */
    public function locales_have_array_access()
    {
        $this->assertEquals('de', $this->locale['code']);
        
        $this->locale['code'] = 'uk';
        $this->assertEquals('uk', $this->locale['code']);

        unset($this->locale['code']);
        $this->assertNull($this->locale['code']);
    }

    /** @test */
    public function listings_can_list_critical_errors()
    {
        $this->assertCount(0, $this->listing->critical());
    }

    /** @test */
    public function listings_can_ignore_critical_errors()
    {
        $this->assertCount(1, $this->listing->critical(false));
    }
}
