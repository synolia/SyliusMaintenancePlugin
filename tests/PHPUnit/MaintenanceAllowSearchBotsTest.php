<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

final class MaintenanceAllowSearchBotsTest extends AbstractWebTestCase
{
    /**
     * @dataProvider provideUserAgent
     */
    public function testMaintenanceAllowBots(bool $isMaintenance, string $userAgent): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'allow_bots' => true,
        ]);

        self::$client->request('GET', '/en_US/', [], [], ['HTTP_USER_AGENT' => $userAgent]);

        $isMaintenance ? $this->assertSiteIsInMaintenance() : $this->assertSiteIsUp();
    }

    public function provideUserAgent(): \Generator
    {
        yield 'crawler : googlebot desktop' => [false, 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Googlebot/2.1; +http://www.google.com/bot.html) Chrome/112.0.0.0 Safari/537.36'];
        yield 'crawler : googlebot smartphone' => [false, 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'];
        yield 'crawler : /bot/' => [false, 'yacybot (-global; amd64 Linux 4.4.0-57-generic; java 9-internal; Europe/en) http://yacy.net/bot.html'];
        yield 'crawler : /crawl/' => [false, 'crawler for netopian (http://www.netopian.co.uk/)'];
        yield 'crawler : /slurp/' => [false, 'Mozilla/5.0 (compatible; Yahoo! Slurp/3.0; http://help.yahoo.com/help/us/ysearch/slurp)'];
        yield 'crawler : /spider/' => [false, 'Mozilla/5.0 (compatible; OpenindexDeepSpider/Nutch-1.5-dev; +http://www.openindex.io/en/webmasters/spider.html; systemsATopenindexDOTio)'];
        yield 'default device' => [true, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36'];
    }
}
