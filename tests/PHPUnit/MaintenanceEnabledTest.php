<?php

declare(strict_types=1);

namespace PHPUnit;

use Symfony\Component\Yaml\Yaml;
use Tests\Synolia\SyliusMaintenancePlugin\PHPUnit\AbstractWebTestCase;

final class MaintenanceEnabledTest extends AbstractWebTestCase
{
    public function testMaintenanceIsEnabledForJsonRequests(): void
    {
        \file_put_contents(
            $this->file,
            Yaml::dump([
                'enabled' => true,
                'custom_message' => 'niwebnimaster',
            ]),
        );
        self::$client->jsonRequest('GET', '/en_US/');

        self::assertResponseStatusCodeSame(503);
        self::assertResponseFormatSame('json');
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertIsArray(json_decode(self::$client->getResponse()->getContent(), true));
        self::assertArrayHasKey('key', json_decode(self::$client->getResponse()->getContent(), true));
        self::assertArrayHasKey('message', json_decode(self::$client->getResponse()->getContent(), true));
        self::assertSame('maintenance', json_decode(self::$client->getResponse()->getContent(), true)['key']);
        self::assertSame('niwebnimaster', json_decode(self::$client->getResponse()->getContent(), true)['message']);
    }
}
