<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

trait AssertTrait
{
    protected function assertSiteIsUp(): void
    {
        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Fashion Web Store');
        self::assertSelectorTextContains('footer', 'Powered by Sylius');
    }

    protected function assertSiteIsInMaintenance(string $message = 'maintenance.ui.message'): void
    {
        $translator = self::getContainer()->get('translator');

        self::assertResponseStatusCodeSame(503);
        self::assertSelectorTextContains('body', $translator->trans($message));
    }
}
