<?php

namespace Kfriars\TranslationsManager\Tests\Traits;

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

trait TestsCommands
{
    /** @var string */
    protected $artisanOutput = '';

    /** @var int */
    protected $result = 1;

    /**
     * Get all output from a non-interactive artisan command
     *
     * @param string $command
     * @param array $args
     * @return TestsCommands
     */
    public function artisanOutput(string $command, array $args = []): self
    {
        $output = new BufferedOutput();

        $this->result = Artisan::call($command, $args, $output);

        $this->artisanOutput = $output->fetch();

        return $this;
    }

    /**
     * Ensure the artisan output contains a string
     *
     * @param string $expected
     * @return self
     */
    public function assertOutputContains($expected): self
    {
        if (! is_array($expected)) {
            $expected = [ $expected ];
        }
        foreach ($expected as $output) {
            $this->assertStringContainsString($output, $this->artisanOutput);
        }

        return $this;
    }

    /**
     * Ensure the artisan output contains a string
     *
     * @param string $expected
     * @return self
     */
    public function assertOutputDoesNotContain($expected): self
    {
        if (! is_array($expected)) {
            $expected = [ $expected ];
        }

        foreach ($expected as $output) {
            $this->assertStringNotContainsString($output, $this->artisanOutput);
        }

        return $this;
    }

    /**
     * Ensure the artisan comand result has succeeded (returns 0)
     *
     * @param string $expected
     * @return self
     */
    public function assertCommandSuccess(): self
    {
        $this->assertEquals(0, $this->result);

        return $this;
    }

    /**
     * Ensure the artisan command failed (returns non-zero)
     *
     * @param string $expected
     * @return self
     */
    public function assertCommandFailure(): self
    {
        $this->assertNotEquals(0, $this->result);

        return $this;
    }
}
