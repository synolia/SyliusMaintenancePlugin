<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\Storage\TokenStorage;

final class TokenStorageController extends AbstractController
{
    public function __construct(
        private MaintenanceConfigurationFactory $configurationFactory,
        private TokenStorage $tokenStorage,
        private TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $maintenanceConfiguration = $this->configurationFactory->get();

        if ($maintenanceConfiguration->isEnabled()) {
            $this->tokenStorage->set($maintenanceConfiguration->getToken());
            $request->getSession()->getFlashBag()->add('success', $this->translator->trans('maintenance.ui.form.token_storage.message'));
        }

        return $this->redirectToRoute('sylius_admin_maintenance_configuration');
    }
}
