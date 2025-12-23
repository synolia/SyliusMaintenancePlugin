<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

final class MaintenanceAllowAccessToAdminAreaTest extends AbstractWebTestCase
{
    public function testMaintenanceDisallowAccessToAdminArea(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'custom_message' => 'Maintenance ON',
            'allow_admins' => false,
        ]);

        self::$client->request('GET', '/admin/login');

        self::assertResponseStatusCodeSame(503);
    }

    public function testMaintenanceAllowAccessToAdminArea(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'custom_message' => 'Maintenance ON',
        ]);

        self::$client->request('GET', '/admin/login');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Sylius | Administration panel login');
    }
}
