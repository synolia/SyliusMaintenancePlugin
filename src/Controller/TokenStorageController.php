<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\Storage\TokenStorage;

final class TokenStorageController extends AbstractController
{
    private MaintenanceConfigurationFactory $configurationFactory;

    private TokenStorage $tokenStorage;

    public function __construct(
        MaintenanceConfigurationFactory $configurationFactory,
        TokenStorage $tokenStorage
    ) {
        $this->configurationFactory = $configurationFactory;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(Request $request): Response
    {
        $maintenanceConfiguration = $this->configurationFactory->get();

        if ($maintenanceConfiguration->isEnabled()) {
            $this->tokenStorage->set($maintenanceConfiguration->getToken());
        }

        return $this->redirectToRoute('sylius_admin_maintenance_configuration');
    }
}
