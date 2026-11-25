<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class MaintenanceCommandsTest extends AbstractWebTestCase
{
    public function testEnableCommandPutsTheSiteInMaintenance(): void
    {
        $this->executeCommand('maintenance:enable');

        $configuration = $this->configurationFileManager->parseMaintenanceYaml();
        self::assertIsArray($configuration);
        self::assertTrue($configuration['enabled']);
        self::assertTrue($configuration['allow_admins']);
        self::assertSame(['FASHION_WEB'], $configuration['channels']);

        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsInMaintenance();
    }

    public function testEnableCommandAllowsTheGivenIps(): void
    {
        $this->executeCommand('maintenance:enable', ['ips_address' => ['10.0.0.1', '127.0.0.1']]);

        self::assertSame(['10.0.0.1', '127.0.0.1'], $this->configurationFileManager->parseMaintenanceYaml()['ips'] ?? null);

        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsUp();
    }

    public function testEnableCommandCanDisableAdminAccess(): void
    {
        $this->executeCommand('maintenance:enable', ['--disable-admin' => true]);

        self::assertFalse($this->configurationFileManager->parseMaintenanceYaml()['allow_admins'] ?? null);

        self::$client->request('GET', '/admin/login');
        self::assertResponseStatusCodeSame(503);
    }

    public function testDisableCommandKeepsTheConfiguration(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'custom_message' => 'Back soon',
            'ips' => ['10.0.0.1'],
        ]);

        $this->executeCommand('maintenance:disable');

        $configuration = $this->configurationFileManager->parseMaintenanceYaml();
        self::assertIsArray($configuration);
        self::assertFalse($configuration['enabled']);
        self::assertSame('Back soon', $configuration['custom_message']);
        self::assertSame(['10.0.0.1'], $configuration['ips']);

        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsUp();
    }

    public function testDisableCommandWithClearOptionRemovesTheConfiguration(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'custom_message' => 'Back soon',
        ]);

        $this->executeCommand('maintenance:disable', ['--clear' => true]);

        self::assertFalse($this->configurationFileManager->hasMaintenanceFile());

        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsUp();
    }

    public function testEnableCommandKeepsThePreviousConfiguration(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'custom_message' => 'Back soon',
            'enabled' => false,
        ]);

        $this->executeCommand('maintenance:enable');

        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsInMaintenance('Back soon');
    }

    private function executeCommand(string $name, array $input = []): void
    {
        $commandTester = new CommandTester((new Application(self::$kernel))->find($name));

        self::assertSame(Command::SUCCESS, $commandTester->execute($input));
    }
}
