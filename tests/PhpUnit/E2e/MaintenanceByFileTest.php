<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PhpUnit\E2e;

use Symfony\Component\Yaml\Yaml;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;

class MaintenanceByFileTest extends AbstractMaintenanceTest
{
    public function testMaintenanceFileExiste(): void
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
        yield [null, (new \DateTime())->modify('-1 day'), false];// already end
        yield [null, (new \DateTime())->modify('+1 day'), true];// end tomorrow
        yield [(new \DateTime())->modify('-10 day'), (new \DateTime())->modify('-1 day'), false];// interval ended
        yield [(new \DateTime())->modify('+1 day'), (new \DateTime())->modify('+2 day'), false];// interval not stated
        yield [(new \DateTime())->modify('-10 day'), (new \DateTime())->modify('+1 day'), true];// in date interval
        yield [(new \DateTime())->modify('+1 day'), null, false];// in the futur
        yield [(new \DateTime())->modify('-1 day'), null, true];// already start

        yield [null, (new \DateTime())->modify('-1 hour'), false];// already end
        yield [null, (new \DateTime())->modify('+1 hour'), true];// end tomorrow
        yield [(new \DateTime())->modify('-10 hour'), (new \DateTime())->modify('-1 hour'), false];// interval ended
        yield [(new \DateTime())->modify('+1 hour'), (new \DateTime())->modify('+2 hour'), false];// interval not stated
        yield [(new \DateTime())->modify('-10 hour'), (new \DateTime())->modify('+1 hour'), true];// in date interval
        yield [(new \DateTime())->modify('+1 hour'), null, false];// in the futur
        yield [(new \DateTime())->modify('-1 hour'), null, true];// already start
    }
}
