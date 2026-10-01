<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests exercise application behavior without requiring a
        // locally generated Vite manifest. Production still runs the real build.
        $this->withoutVite();
    }
}
