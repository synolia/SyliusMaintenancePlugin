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

class AdminChecker implements IsMaintenanceCheckerInterface
{
    public function __construct(
        protected ParameterBagInterface $params,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
    ) {
    }

    public static function getDefaultPriority(): int
    {
        return 70;
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        $getRequestUri = $request->getRequestUri();
        /** @var string $adminPrefix */
        $adminPrefix = $this->params->get('sylius_admin.path_name');

        if (str_starts_with($getRequestUri, '/_profiler') || str_starts_with($getRequestUri, '/_wdt')) {
            return IsMaintenanceVoterInterface::ACCESS_GRANTED;
        }

        if (false !== mb_strpos($getRequestUri, $adminPrefix, 1)) {
            $session = null;
            if (method_exists($this->requestStack, 'isMainRequest')) {
                if (!$this->requestStack->isMainRequest()) {
                    return IsMaintenanceVoterInterface::ACCESS_DENIED;
                }

                $session = $this->requestStack->getMainRequest()?->getSession();
            }

            /** @TODO Drop after remove Symfony 4.4 compatibility */
            if (method_exists($this->requestStack, 'isMasterRequest')) {
                if (!$this->requestStack->isMasterRequest()) {
                    return IsMaintenanceVoterInterface::ACCESS_DENIED;
                }

                $session = $this->requestStack->getMasterRequest()?->getSession();
            }

            if (null !== $session) {
                /** @var FlashBagInterface $flashBag */
                $flashBag = $session->getBag('flashes');
                $flashBag->add('info', $this->translator->trans('maintenance.ui.message_info_admin'));
            }

            return IsMaintenanceVoterInterface::ACCESS_GRANTED;
        }

        return IsMaintenanceVoterInterface::ACCESS_DENIED;
    }
}
