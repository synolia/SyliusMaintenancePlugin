<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

final class MaintenanceNotEnabledTest extends AbstractWebTestCase
{
    public function testMaintenanceIsNotEnabledWhenFileDoesNotExist(): void
    {
        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsUp();
    }

    public function testMaintenanceIsNotEnabledWhenFileIsNotEnabled(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'enabled' => false,
        ]);
        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsUp();
    }
}
