<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Synolia\SyliusMaintenancePlugin\Creator\CookieCreator;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;

final class GenerateCookieController extends AbstractController
{
    private MaintenanceConfigurationFactory $configurationFactory;

    private CookieCreator $cookieCreator;

    public function __construct(
        MaintenanceConfigurationFactory $configurationFactory,
        CookieCreator $cookieCreator
    ) {
        $this->configurationFactory = $configurationFactory;
        $this->cookieCreator = $cookieCreator;
    }

    public function __invoke(): Response
    {
        $maintenanceConfiguration = $this->configurationFactory->get();

        $response = $this->redirectToRoute('sylius_admin_maintenance_configuration');

        if ($maintenanceConfiguration->isEnabled()) {
            $response->headers->setCookie($this->cookieCreator->create($maintenanceConfiguration->getToken()));
        }

        return $response;
    }
}
