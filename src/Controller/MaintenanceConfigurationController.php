<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Creator\CookieCreator;
use Synolia\SyliusMaintenancePlugin\Exporter\MaintenanceConfigurationExporter;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\Form\Type\MaintenanceConfigurationType;

final class MaintenanceConfigurationController extends AbstractController
{
    private TranslatorInterface $translator;

    private FlashBagInterface $flashBag;

    private MaintenanceConfigurationExporter $maintenanceExporter;

    private MaintenanceConfigurationFactory $configurationFactory;

    private CookieCreator $cookieCreator;

    public function __construct(
        FlashBagInterface $flashBag,
        TranslatorInterface $translator,
        MaintenanceConfigurationExporter $maintenanceExporter,
        MaintenanceConfigurationFactory $configurationFactory,
        CookieCreator $cookieCreator
    ) {
        $this->flashBag = $flashBag;
        $this->translator = $translator;
        $this->maintenanceExporter = $maintenanceExporter;
        $this->configurationFactory = $configurationFactory;
        $this->cookieCreator = $cookieCreator;
    }

    public function __invoke(Request $request): Response
    {
        $maintenanceConfiguration = $this->configurationFactory->get();

        $form = $this->createForm(MaintenanceConfigurationType::class, $maintenanceConfiguration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $maintenanceConfiguration->getEndDate() && $maintenanceConfiguration->getEndDate() < (new \DateTime())) {
                $maintenanceConfiguration->setEnabled(false);
                $this->flashBag->add('error', $this->translator->trans('maintenance.ui.message_end_date_in_the_past'));
            }

            $this->maintenanceExporter->export($maintenanceConfiguration);
            $message = 'maintenance.ui.message_disabled';
            if ($maintenanceConfiguration->isEnabled()) {
                $message = 'maintenance.ui.message_enabled';
            }

            $this->flashBag->add('success', $this->translator->trans($message));
        }

        $response = $this->render('@SynoliaSyliusMaintenancePlugin/Admin/maintenanceConfiguration.html.twig', [
            'form' => $form->createView(),
        ]);

        if ($maintenanceConfiguration->isEnabled()) {
            $response->headers->setCookie($this->cookieCreator->create($maintenanceConfiguration->getToken()));
        }

        return $response;
    }
}
