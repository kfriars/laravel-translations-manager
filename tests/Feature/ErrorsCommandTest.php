<?php

namespace Kfriars\TranslationsManager\Tests\Feature;

use Kfriars\TranslationsManager\Tests\TestCase;

class ErrorsCommandTest extends TestCase
{
    /** @test */
    public function it_outputs_the_errors_command_correctly()
    {
        $this->loadScenario($this->app, 'missing_strings');

        $this->artisanOutput('translations:errors')
            ->assertCommandFailure()
            ->assertOutputContains([
                '+------------+---------------------+',
                '| de/missing_keys                  |',
                '+------------+---------------------+',
                '| Key        | Message             |',
                '+------------+---------------------+',
                '| de.missing | translation_missing |',
                '+------------+---------------------+',
            ])
            ->assertOutputContains([
                '+-------------+---------------------+',
                '| de/nested/missing_keys            |',
                '+-------------+---------------------+',
                '| Key         | Message             |',
                '+-------------+---------------------+',
                '| de.missing  | translation_missing |',
                '+-------------+---------------------+',
            ])
            ->assertOutputContains([
                '+------------------+---------------------+',
                '| es/missing_keys                        |',
                '+------------------+---------------------+',
                '| Key              | Message             |',
                '+------------------+---------------------+',
                '| de.fr.es.missing | translation_missing |',
                '+------------------+---------------------+',
            ])
            ->assertOutputContains([
                '+-------------+---------------------+',
                '| es/nested/missing_keys            |',
                '+-------------+---------------------+',
                '| Key         | Message             |',
                '+-------------+---------------------+',
                '| es.missing  | translation_missing |',
                '+-------------+---------------------+',
            ])
            ->assertOutputContains([
                '+---------------+---------------------+',
                '| fr/missing_keys                     |',
                '+---------------+---------------------+',
                '| Key           | Message             |',
                '+---------------+---------------------+',
                '| de.fr.missing | translation_missing |',
                '+---------------+---------------------+',
            ])
            ->assertOutputContains([
                '+-------------+---------------------+',
                '| fr/nested/missing_keys            |',
                '+-------------+---------------------+',
                '| Key         | Message             |',
                '+-------------+---------------------+',
                '| fr.missing  | translation_missing |',
                '+-------------+---------------------+',
            ]);
    }

    /** @test */
    public function it_writes_a_success_message_when_there_are_no_errors()
    {
        $this->artisanOutput('translations:errors')
            ->assertCommandSuccess()
            ->assertOutputContains('There are no errors in the translations files!');
    }

    /** @test */
    public function it_writes_translations_manager_exceptions_as_error_output()
    {
        $this->artisanOutput('translations:errors en')
            ->assertCommandFailure()
            ->assertOutputContains("You can not use the translations manager with the reference locale 'en'");
    }
}
