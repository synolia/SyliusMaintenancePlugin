<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Exporter\MaintenanceConfigurationExporter;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\Form\Type\MaintenanceConfigurationType;

final class MaintenanceConfigurationController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
        private MaintenanceConfigurationExporter $maintenanceExporter,
        private MaintenanceConfigurationFactory $configurationFactory,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $maintenanceConfiguration = $this->configurationFactory->get();

        $form = $this->createForm(MaintenanceConfigurationType::class, $maintenanceConfiguration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $maintenanceConfiguration->getEndDate() && $maintenanceConfiguration->getEndDate() < (new \DateTime())) {
                $maintenanceConfiguration->setEnabled(false);
                $request->getSession()->getFlashBag()->add('error', $this->translator->trans('maintenance.ui.message_end_date_in_the_past'));
            }

            $this->maintenanceExporter->export($maintenanceConfiguration);
            $message = 'maintenance.ui.message_disabled';
            if ($maintenanceConfiguration->isEnabled()) {
                $message = 'maintenance.ui.message_enabled';
            }

            $request->getSession()->getFlashBag()->add('success', $this->translator->trans($message));
        }

        return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/maintenanceConfiguration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
