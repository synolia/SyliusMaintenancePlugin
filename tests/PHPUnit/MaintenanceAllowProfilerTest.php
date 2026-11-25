<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

final class MaintenanceAllowProfilerTest extends AbstractWebTestCase
{
    /** @dataProvider provideUrls */
    public function testProfilerIsReachableDuringMaintenance(string $url, bool $isMaintenance): void
    {
        $this->configurationFileManager->createMaintenanceFile([]);

        // The profiler is disabled in the test environment, so the voter is asked directly
        self::assertSame($isMaintenance, $this->isMaintenance(Request::create($url)));
    }

    public function provideUrls(): \Generator
    {
        yield 'profiler' => ['http://localhost/_profiler/123456', false];
        yield 'web debug toolbar' => ['http://localhost/_wdt/123456', false];
        yield 'shop' => ['http://localhost/en_US/', true];
    }

    private function isMaintenance(Request $request): bool
    {
        // TokenChecker reads the session, which the kernel would normally attach to the request
        $request->setSession(new Session(new MockArraySessionStorage()));
        $requestStack = self::getContainer()->get(RequestStack::class);
        $requestStack->push($request);

        try {
            return self::getContainer()->get(IsMaintenanceVoterInterface::class)->isMaintenance(
                self::getContainer()->get(MaintenanceConfigurationFactory::class)->get(),
                $request,
            );
        } finally {
            $requestStack->pop();
        }
    }
}
