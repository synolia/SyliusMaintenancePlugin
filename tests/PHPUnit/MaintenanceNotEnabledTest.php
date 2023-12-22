<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

use Symfony\Component\Yaml\Yaml;

final class MaintenanceNotEnabledTest extends AbstractWebTestCase
{
    public function testMaintenanceIsNotEnabledWhenFileDoesNotExist(): void
    {
        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsUp();
    }

    public function testMaintenanceIsNotEnabledWhenFileIsNotEnabled(): void
    {
        \file_put_contents(
            $this->file,
            Yaml::dump([
                'enabled' => false,
            ]),
        );
        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsUp();
    }
}
