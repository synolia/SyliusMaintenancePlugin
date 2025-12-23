<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;

abstract class AbstractWebTestCase extends WebTestCase
{
    use AssertTrait;

    protected ConfigurationFileManager $configurationFileManager;

    protected static ?AbstractBrowser $client = null;

    protected EntityManagerInterface $manager;

    protected function setUp(): void
    {
        if (!self::$client instanceof AbstractBrowser) {
            self::$client = self::createClient();
        }
        $this->configurationFileManager = self::getContainer()->get(ConfigurationFileManager::class);
        $this->configurationFileManager->deleteMaintenanceFile();

        $this->manager = self::getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        $this->configurationFileManager->deleteMaintenanceFile();
    }
}
