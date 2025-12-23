<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Synolia\SyliusMaintenancePlugin\Storage\TokenStorage;

final class MaintenanceAllowAccessByTokenTest extends AbstractWebTestCase
{
    /** @dataProvider tokens */
    public function testMaintenanceFileWithToken(
        string $configurationToken,
        string $token,
        bool $isGenerated,
        bool $isInMaintenance,
        array $params = [],
        array $headers = [],
    ): void {
        $this->configurationFileManager->createMaintenanceFile([
            'token' => $configurationToken,
        ]);

        if ($isGenerated) {
            $this->createSession(self::$client, $token);
        }

        self::$client->request('GET', '/en_US/', $params, [], $headers);

        if ($isInMaintenance) {
            $this->assertSiteIsInMaintenance();
        } else {
            $this->assertSiteIsUp();
        }
    }

    public function tokens(): \Generator
    {
        // configurationToken, token, isGenerated, isInMaintenance
        yield 'same tokens, and token is generated, so access allowed' => [
            '63454fe526b4102103f76a4dbbd442e3', '63454fe526b4102103f76a4dbbd442e3', true, false,
        ];
        yield 'same tokens, and token is NOT generated, so no access to website' => [
            'AnotherToken', 'AnotherToken', false, true,
        ];
        yield 'token generated is not that of the maintenance file, so no access to website' => [
            '63454fe526b4102103f76a4dbbd442e3', 'token123', true, true,
        ];
        yield 'token is not generated but queryParams are matching, so access allowed' => [
            'g00d_s$cr3t', '', false, false, ['synolia_maintenance_token' => 'g00d_s$cr3t'],
        ];
        yield 'token is not generated but headers are matching, so access allowed' => [
            'g00d_s$cr3t', '', false, false, [], ['HTTP_SYNOLIA_MAINTENANCE_TOKEN' => 'g00d_s$cr3t'],
        ];
        yield 'token is not generated but queryParams are not matching, so no access to website' => [
            'g00d_s$cr3t', '', false, true, ['synolia_maintenance_token' => '0th4r_s$cr3t'],
        ];
        yield 'token is generated and same token, so access to website even if queryParams are not matching' => [
            'g00d_s$cr3t', 'g00d_s$cr3t', true, false, ['synolia_maintenance_token' => '0th4r_s$cr3t'],
        ];
    }

    private function createSession(KernelBrowser $client, string $token): Session
    {
        $sessionStorage = new MockFileSessionStorage('var/cache/test/sessions');

        $session = new Session($sessionStorage);

        $session->start();
        $session->set(TokenStorage::MAINTENANCE_TOKEN_NAME, $token);
        $session->save();

        $sessionCookie = new Cookie(
            $session->getName(),
            $session->getId(),
            null,
            null,
            'localhost',
        );
        $client->getCookieJar()->set($sessionCookie);

        return $session;
    }
}
