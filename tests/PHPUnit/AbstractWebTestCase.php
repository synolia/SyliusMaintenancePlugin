<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionClassConstant;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;

abstract class AbstractWebTestCase extends WebTestCase
{
    use AssertTrait;

    protected string $file;

    protected static ?AbstractBrowser $client = null;

    protected EntityManagerInterface $manager;

    protected function setUp(): void
    {
        /** @var ReflectionClassConstant $constant */
        $constant = (new ReflectionClass(ConfigurationFileManager::class))
            ->getReflectionConstant('MAINTENANCE_FILE')
        ;
        $this->file = $constant->getValue();
        @\unlink($this->file);

        if (!self::$client instanceof AbstractBrowser) {
            self::$client = self::createClient();
        }

        $this->manager = self::$kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        @\unlink($this->file);
    }
}
