<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PhpUnit\E2e;

use Symfony\Component\Yaml\Yaml;

class MaintenanceByFileTest extends AbstractMaintenanceTest
{
    public function testMaintenanceFileExiste(): void
    {
        \touch('maintenance.yaml');
        $client = static::createPantherClient();
        $client->request('GET', '/');

        $this->assertSelectorTextContains('body', 'The website is under maintenance');
    }

    /** @dataProvider listOfIps */
    public function testMaintenanceFileWithIp(array $ips, bool $maintenance): void
    {
        \file_put_contents(
            'maintenance.yaml',
            Yaml::dump([
                'ips' => $ips
            ])
        );

        $client = static::createPantherClient();
        $client->request('GET', '/');

        if ($maintenance) {
            $this->assertSelectorTextContains('body', 'The website is under maintenance');
        } else {
            $this->assertPageTitleContains('Boutique Web');
            $this->assertSelectorTextContains('#footer', 'Powered by Sylius');
        }

    }

    public function listOfIps(): \Generator
    {
        // ips[], isInMaintenance
        yield [['127.0.0.1'], false];
        yield [['10.0.0.1', '127.0.0.1'], false];
        yield [['127.0.0.1', '10.0.0.1'], false];
        yield [['123.123.123.123'], true];
        yield [['123.123.123.123', '10.0.0.1'], true];
    }
}
