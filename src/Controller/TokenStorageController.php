<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\Storage\TokenStorage;

final class TokenStorageController extends AbstractController
{
    private MaintenanceConfigurationFactory $configurationFactory;

    private TokenStorage $tokenStorage;

    private FlashBagInterface $flashBag;

    private TranslatorInterface $translator;

    public function __construct(
        MaintenanceConfigurationFactory $configurationFactory,
        TokenStorage $tokenStorage,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->configurationFactory = $configurationFactory;
        $this->tokenStorage = $tokenStorage;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    public function __invoke(Request $request): Response
    {
        $maintenanceConfiguration = $this->configurationFactory->get();

        if ($maintenanceConfiguration->isEnabled()) {
            $this->tokenStorage->set($maintenanceConfiguration->getToken());
            $this->flashBag->add('success', $this->translator->trans('maintenance.ui.form.token_storage.message'));
        }

        return $this->redirectToRoute('sylius_admin_maintenance_configuration');
    }
}
