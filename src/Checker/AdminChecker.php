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
    protected ParameterBagInterface $params;

    protected RequestStack $requestStack;

    protected FlashBagInterface $flashBag;

    protected TranslatorInterface $translator;

    public function __construct(
        ParameterBagInterface $params,
        RequestStack $requestStack,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->params = $params;
        $this->requestStack = $requestStack;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
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

        if (false !== mb_strpos($getRequestUri, $adminPrefix, 1)) {
            if ($this->requestStack->getMainRequest() === $this->requestStack->getCurrentRequest()) {
                $this->flashBag->add('info', $this->translator->trans('maintenance.ui.message_info_admin'));
            }

            return IsMaintenanceVoterInterface::ACCESS_GRANTED;
        }

        return IsMaintenanceVoterInterface::ACCESS_DENIED;
    }
}
