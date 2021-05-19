<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PhpUnit\E2e;

final class MaintenanceNotEnabledTest extends AbstractWebTestCase
{
    public function testMaintenanceIsNotEnabled(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/');

        $this->assertSiteIsUp();
    }
}
