<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\Storage\TokenStorage;

#[AsController]
final class TokenStorageController extends AbstractController
{
    public function __construct(
        private readonly MaintenanceConfigurationFactory $configurationFactory,
        private readonly TokenStorage $tokenStorage,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/maintenance/token-storage', name: 'sylius_admin_maintenance_token_storage', methods: ['GET', 'POST'])]
    public function __invoke(): Response
    {
        $maintenanceConfiguration = $this->configurationFactory->get();

        if ($maintenanceConfiguration->isEnabled()) {
            $this->tokenStorage->set($maintenanceConfiguration->getToken());
            $this->addFlash('success', $this->translator->trans('maintenance.ui.form.token_storage.message'));
        }

        return $this->redirectToRoute('sylius_admin_maintenance_configuration');
    }
}
