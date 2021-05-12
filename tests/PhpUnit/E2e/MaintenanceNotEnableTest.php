<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PhpUnit\E2e;

class MaintenanceNotEnableTest extends AbstractMaintenanceTest
{
    public function testMaintenanceIsNotEnable(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/');

        $this->assertPageTitleContains('Boutique Web');
        $this->assertSelectorTextContains('#footer', 'Powered by Sylius');
    }
}
