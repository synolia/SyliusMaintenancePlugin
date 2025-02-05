<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;
use Webmozart\Assert\Assert;

class AdminChecker implements IsMaintenanceCheckerInterface
{
    private const PRIORITY = 70;

    public function __construct(
        protected ParameterBagInterface $params,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
    ) {
    }

    public static function getDefaultPriority(): int
    {
        return self::PRIORITY;
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        $getRequestUri = $request->getRequestUri();
        /** @var string $adminPrefix */
        $adminPrefix = $this->params->get('sylius_admin.path_name');
        $adminPrefix = \DIRECTORY_SEPARATOR . $adminPrefix;

        if (str_starts_with($getRequestUri, $adminPrefix)) {
            $request = $this->requestStack->getMainRequest();
            Assert::isInstanceOf($request, Request::class);

            if ($configuration->isAllowAdmins()) {
                if ($request === $this->requestStack->getCurrentRequest()) {
                    $flashBag = $request->getSession()->getBag('flashes');
                    if ($flashBag instanceof FlashBagInterface) {
                        $flashBag->add('info', $this->translator->trans('maintenance.ui.message_info_admin'));
                    }
                }

                return IsMaintenanceVoterInterface::ACCESS_GRANTED;
            }
        }

        return IsMaintenanceVoterInterface::ACCESS_DENIED;
    }
}
