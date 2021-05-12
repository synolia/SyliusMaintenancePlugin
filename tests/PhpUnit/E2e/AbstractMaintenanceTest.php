<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PhpUnit\E2e;

use Symfony\Component\Panther\PantherTestCase;

abstract class AbstractMaintenanceTest extends PantherTestCase
{
    protected function setUp(): void
    {
        @\unlink('maintenance.yaml');
    }

    protected function tearDown(): void
    {
        @\unlink('maintenance.yaml');
    }
}
