<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PhpUnit\E2e;

use ReflectionClass;
use ReflectionClassConstant;
use Symfony\Component\Panther\PantherTestCase;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;

abstract class AbstractWebTestCase extends PantherTestCase
{
    protected string $file;

    protected function setUp(): void
    {
        $constant = (new ReflectionClass(ConfigurationFileManager::class))
        ->getReflectionConstant('MAINTENANCE_FILE');

        self::assertInstanceOf(ReflectionClassConstant::class, $constant);

        $this->file = $constant->getValue();

        self::assertIsString($this->file);

        @\unlink($this->file);
    }

    protected function tearDown(): void
    {
        @\unlink($this->file);
    }

    protected function assertSiteIsUp(): void
    {
        self::assertPageTitleContains('Boutique Web');
        self::assertSelectorTextContains('#footer', 'Powered by Sylius');
    }

    protected function assertSiteIsInMaintenance(string $message = 'The website is under maintenance'): void
    {
        self::assertSelectorTextContains('body', $message);
    }
}
