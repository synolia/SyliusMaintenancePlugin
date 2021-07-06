<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

final class MaintenanceNotEnabledTest extends AbstractWebTestCase
{
    public function testMaintenanceIsNotEnabled(): void
    {
        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsUp();
    }
}
