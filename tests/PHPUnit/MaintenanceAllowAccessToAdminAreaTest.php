<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

use ReflectionClass;
use ReflectionClassConstant;
use Symfony\Component\Yaml\Yaml;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;

final class MaintenanceAllowAccessToAdminAreaTest extends AbstractWebTestCase
{
    use AssertTrait;

    public function testMaintenanceDisallowAccessToAdminArea(): void
    {
        /** @var ReflectionClassConstant $constant */
        $constant = (new ReflectionClass(ConfigurationFileManager::class))
            ->getReflectionConstant('MAINTENANCE_FILE')
        ;
        $file = $constant->getValue();

        \file_put_contents(
            $file,
            Yaml::dump([
                'custom_message' => 'Maintenance ON',
            ]),
        );

        self::$client->request('GET', '/admin/login');

        self::assertResponseStatusCodeSame(503);
    }

    public function testMaintenanceAllowAccessToAdminArea(): void
    {
        /** @var ReflectionClassConstant $constant */
        $constant = (new ReflectionClass(ConfigurationFileManager::class))
            ->getReflectionConstant('MAINTENANCE_FILE')
        ;
        $file = $constant->getValue();

        \file_put_contents(
            $file,
            Yaml::dump([
                'custom_message' => 'Maintenance ON',
                'allow_admins' => true,
            ]),
        );

        self::$client->request('GET', '/admin/login');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Sylius | Administration panel login');
    }
}
