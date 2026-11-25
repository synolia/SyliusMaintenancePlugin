<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Component\DomCrawler\Form;

final class MaintenanceAdministrationTest extends AbstractWebTestCase
{
    private const CONFIGURATION_URL = '/admin/maintenance/configuration';

    protected function setUp(): void
    {
        parent::setUp();

        $adminUser = self::getContainer()->get('sylius.repository.admin_user')->findOneBy(['username' => 'sylius']);
        self::assertInstanceOf(AdminUserInterface::class, $adminUser);
        self::$client->loginUser($adminUser, 'admin');
    }

    protected function tearDown(): void
    {
        // The client is shared between test classes: do not leak the admin session
        self::$client->restart();

        parent::tearDown();
    }

    public function testConfigurationPageIsDisplayed(): void
    {
        self::$client->request('GET', self::CONFIGURATION_URL);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="maintenance_configuration"]');
    }

    public function testMaintenanceIsEnabledFromTheConfigurationPage(): void
    {
        $form = $this->getConfigurationForm();
        $form['maintenance_configuration[enabled]']->tick();
        $form['maintenance_configuration[customMessage]'] = 'Back soon';
        $form['maintenance_configuration[ipAddresses]'] = '10.0.0.1, 10.0.0.2';
        $form['maintenance_configuration[allowBots]']->tick();
        self::$client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::getContainer()->get('translator')->trans('maintenance.ui.message_enabled'));

        $configuration = $this->configurationFileManager->parseMaintenanceYaml();
        self::assertIsArray($configuration);
        self::assertTrue($configuration['enabled']);
        self::assertSame('Back soon', $configuration['custom_message']);
        self::assertSame(['10.0.0.1', '10.0.0.2'], $configuration['ips']);
        self::assertTrue($configuration['allow_bots']);
        self::assertNotEmpty($configuration['token']);

        // Admins only bypass the maintenance in the admin area.
        // A browser user agent is needed since bots are allowed and "Symfony BrowserKit" is detected as a bot.
        self::$client->request('GET', '/en_US/', [], [], ['HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36']);
        $this->assertSiteIsInMaintenance('Back soon');
    }

    public function testMaintenanceIsDisabledFromTheConfigurationPage(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'custom_message' => 'Back soon',
        ]);

        $form = $this->getConfigurationForm();
        $form['maintenance_configuration[enabled]']->untick();
        self::$client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::getContainer()->get('translator')->trans('maintenance.ui.message_disabled'));
        self::assertFalse($this->configurationFileManager->parseMaintenanceYaml()['enabled'] ?? null);

        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsUp();
    }

    public function testMaintenanceWithAnEndDateInThePastIsNotEnabled(): void
    {
        $yesterday = new \DateTime('-1 day');

        $form = $this->getConfigurationForm();
        $form['maintenance_configuration[enabled]']->tick();
        $form['maintenance_configuration[endDate][date]'] = $yesterday->format('Y-m-d');
        $form['maintenance_configuration[endDate][time]'] = $yesterday->format('H:i');
        self::$client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::getContainer()->get('translator')->trans('maintenance.ui.message_end_date_in_the_past'));
        self::assertFalse($this->configurationFileManager->parseMaintenanceYaml()['enabled'] ?? null);

        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsUp();
    }

    public function testAdminCanAccessTheShopWithTheStoredToken(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'token' => 'g00d_s3cr3t',
        ]);

        self::$client->request('GET', '/admin/maintenance/token-storage');
        self::assertResponseRedirects(self::CONFIGURATION_URL);

        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsUp();

        // Without the token in session, the shop is still in maintenance
        self::$client->restart();
        self::$client->request('GET', '/en_US/');
        $this->assertSiteIsInMaintenance();
    }

    private function getConfigurationForm(): Form
    {
        $crawler = self::$client->request('GET', self::CONFIGURATION_URL);
        self::assertResponseIsSuccessful();

        return $crawler->filter('form[name="maintenance_configuration"]')->form();
    }
}
