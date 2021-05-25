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
        /** @var ReflectionClassConstant $constant */
        $constant = (new ReflectionClass(ConfigurationFileManager::class))
            ->getReflectionConstant('MAINTENANCE_FILE');
        $this->file = $constant->getValue();
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
