<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PhpUnit\E2e;

use Symfony\Component\Yaml\Yaml;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;

final class WebByFileTestCaseTest extends AbstractWebTestCase
{
    public function testMaintenanceEnabledWhenFileExist(): void
    {
        \touch($this->file);
        $client = static::createPantherClient();
        $client->request('GET', '/');

        $this->assertSiteIsInMaintenance();
    }

    /** @dataProvider listOfIps */
    public function testMaintenanceFileWithIp(array $ips, bool $maintenance): void
    {
        \file_put_contents(
            $this->file,
            Yaml::dump([
                'ips' => $ips
            ])
        );

        $client = static::createPantherClient();
        $client->request('GET', '/');

        if ($maintenance) {
            $this->assertSiteIsInMaintenance();
        } else {
            $this->assertSiteIsUp();
        }
    }

    public function testMaintenanceFileWithMessage(): void
    {
        $message = 'Maintenance custom message.';
        \file_put_contents(
            $this->file,
            Yaml::dump([
                'custom_message' => $message
            ])
        );

        $client = static::createPantherClient();
        $client->request('GET', '/');

        $this->assertSiteIsInMaintenance($message);
    }

    /** @dataProvider generateDates */
    public function testMaintenanceFileWithScheduler(
        ?\DateTime $startDate,
        ?\DateTime $endDate,
        bool $maintenance
    ): void {
        $scheduler = [];
        if (null !== $startDate) {
            $scheduler['start_date'] = $startDate->format('Y-m-d H:i:s');
        }
        if (null !== $endDate) {
            $scheduler['end_date'] = $endDate->format('Y-m-d H:i:s');
        }

        \file_put_contents(
            $this->file,
            Yaml::dump([
                'scheduler' => $scheduler
            ])
        );

        $client = static::createPantherClient();
        $client->request('GET', '/');

        if ($maintenance) {
            $this->assertSiteIsInMaintenance();
        } else {
            $this->assertSiteIsUp();
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

    public function generateDates(): \Generator
    {
        // ?\DateTime $startDate, ?\DateTime $endDate, bool $maintenance
        yield 'Maintenance already ended' => [null, (new \DateTime())->modify('-1 day'), false];
        yield 'Maintenance end tomorrow' => [null, (new \DateTime())->modify('+1 day'), true];
        yield 'Maintenance interval ended' => [(new \DateTime())->modify('-10 day'), (new \DateTime())->modify('-1 day'), false];
        yield 'Maintenance interval not stated' => [(new \DateTime())->modify('+1 day'), (new \DateTime())->modify('+2 day'), false];
        yield 'Maintenance in date interval' => [(new \DateTime())->modify('-10 day'), (new \DateTime())->modify('+1 day'), true];
        yield 'Maintenance in the futur' => [(new \DateTime())->modify('+1 day'), null, false];
        yield 'Maintenance already start' => [(new \DateTime())->modify('-1 day'), null, true];

        yield 'Maintenance ended 1 hour ago' => [null, (new \DateTime())->modify('-1 hour'), false];
        yield 'Maintenance end in 1 hour' => [null, (new \DateTime())->modify('+1 hour'), true];
        yield 'Maintenance interval in hours ended' => [(new \DateTime())->modify('-10 hour'), (new \DateTime())->modify('-1 hour'), false];
        yield 'Maintenance interval in hours not stated' => [(new \DateTime())->modify('+1 hour'), (new \DateTime())->modify('+2 hour'), false];
        yield 'Maintenance in hours interval' => [(new \DateTime())->modify('-10 hour'), (new \DateTime())->modify('+1 hour'), true];
        yield 'Maintenance in 1 hour' => [(new \DateTime())->modify('+1 hour'), null, false];
        yield 'Maintenance started 1 hour ago' => [(new \DateTime())->modify('-1 hour'), null, true];
    }
}
