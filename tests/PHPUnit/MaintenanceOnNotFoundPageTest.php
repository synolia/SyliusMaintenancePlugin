<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

final class MaintenanceOnNotFoundPageTest extends AbstractWebTestCase
{
    public function testMaintenanceIsDisplayedOnAPageThatDoesNotExist(): void
    {
        $this->configurationFileManager->createMaintenanceFile([]);

        self::$client->request('GET', '/en_US/this-page-does-not-exist');

        $this->assertSiteIsInMaintenance();
    }

    public function testMaintenanceIsReturnedForJsonRequestsOnAPageThatDoesNotExist(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'custom_message' => 'niwebnimaster',
        ]);

        self::$client->jsonRequest('GET', '/en_US/this-page-does-not-exist');

        self::assertResponseStatusCodeSame(503);
        self::assertSame(
            ['key' => 'maintenance', 'message' => 'niwebnimaster'],
            json_decode((string) self::$client->getResponse()->getContent(), true),
        );
    }

    public function testPageThatDoesNotExistIsStillNotFoundWithoutMaintenance(): void
    {
        self::$client->request('GET', '/en_US/this-page-does-not-exist');

        self::assertResponseStatusCodeSame(404);
    }

    public function testPageThatDoesNotExistIsStillNotFoundForAnAuthorizedIp(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'ips' => ['127.0.0.1'],
        ]);

        self::$client->request('GET', '/en_US/this-page-does-not-exist');

        self::assertResponseStatusCodeSame(404);
    }
}
