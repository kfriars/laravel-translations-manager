<?php

namespace Kfriars\TranslationsManager\Tests\Feature;

use Kfriars\TranslationsManager\Tests\TestCase;

class StatusCommandTest extends TestCase
{
    /** @test */
    public function it_outputs_the_status_command_correctly()
    {
        $this->loadScenario($this->app, 'ignores_errors');

        $this->artisanOutput('translations:status')
            ->assertCommandSuccess()
            ->assertOutputContains([
                '+--------------------------+------------+-------------------------------+---------+',
                '| Locale: de                                                                      |',
                '+--------------------------+------------+-------------------------------+---------+',
                '| File                     | Status                                     | Ignored |',
                '+--------------------------+------------+-------------------------------+---------+',
                '| common                   | last_name  | translation_missing           | ✓       |',
                '|                          | status     | translation_missing           | ✓       |',
                '| mail/auth/password_reset | FILE_ERROR | file_not_translated           | ✓       |',
                '| mail/auth/registration   | extra      | no_reference_translation      | ✓       |',
                '|                          | body       | incorrect_translation_type    | ✓       |',
                '| mail/leads/newsletter    | title      | reference_translation_updated | ✓       |',
                '+--------------------------+------------+-------------------------------+---------+',
             ])
             ->assertOutputContains([
                '+--------------------------+------------+----------------------------+---------+',
                '| Locale: es                                                                   |',
                '+--------------------------+------------+----------------------------+---------+',
                '| File                     | Status                                  | Ignored |',
                '+--------------------------+------------+----------------------------+---------+',
                '| common                   | extra      | no_reference_translation   | ✓       |',
                '|                          | status.ooo | translation_missing        | ✓       |',
                '| mail/auth/password_reset | title      | translation_missing        | ✓       |',
                '| mail/auth/registration   | subject    | incorrect_translation_type | ✓       |',
                '| mail/leads/newsletter    | FILE_ERROR | file_not_translated        | ✓       |',
                '+--------------------------+------------+----------------------------+---------+',
             ])
             ->assertOutputContains([
                '+--------------------------+------------+-------------------------------+---------+',
                '| Locale: fr                                                                      |',
                '+--------------------------+------------+-------------------------------+---------+',
                '| File                     | Status                                     | Ignored |',
                '+--------------------------+------------+-------------------------------+---------+',
                '| common                   | FILE_ERROR | file_not_translated           | ✓       |',
                '| mail/auth/password_reset | subject    | translation_missing           | ✓       |',
                '| mail/auth/registration   | title      | incorrect_translation_type    | ✓       |',
                '| mail/leads/newsletter    | title      | reference_translation_updated | ✓       |',
                '|                          | extra      | no_reference_translation      | ✓       |',
                '+--------------------------+------------+-------------------------------+---------+',
             ]);
    }

    /** @test */
    public function it_shows_files_without_errors_with_a_checkmark()
    {
        $this->artisanOutput('translations:status de')
             ->assertCommandSuccess()
             ->assertOutputContains([
                '+-------+-----+-----+---------+',
                '| Locale: de                  |',
                '+-------+-----+-----+---------+',
                '| File  | Status    | Ignored |',
                '+-------+-----+-----+---------+',
                '| a/a   | ✓         |         |',
                '| a/b/b | ✓         |         |',
                '| c/c   | ✓         |         |',
                '| d     | ✓         |         |',
                '+-------+-----+-----+---------+',
             ]);
    }

    /** @test */
    public function it_accepts_locales_as_arguments()
    {
        $this->loadScenario($this->app, 'ignores_errors');

        $this->artisanOutput('translations:status de es')
            ->assertCommandSuccess()
            ->assertOutputContains('Locale: de')
            ->assertOutputContains('Locale: es')
            ->assertOutputDoesNotContain('Locale: fr');
    }

    /** @test */
    public function it_writes_translations_manager_exceptions_as_errors()
    {
        $this->artisanOutput('translations:status en')
            ->assertCommandFailure()
            ->assertOutputContains("You can not use the translations manager with the reference locale 'en'");
    }
}
