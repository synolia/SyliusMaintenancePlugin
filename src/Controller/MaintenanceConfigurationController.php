<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Exporter\MaintenanceConfigurationExporter;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Form\Type\MaintenanceConfigurationType;

final class MaintenanceConfigurationController extends AbstractController
{
    private TranslatorInterface $translator;

    private FlashBagInterface $flashBag;

    private ConfigurationFileManager $configurationFileManager;

    private MaintenanceConfigurationExporter $maintenanceExporter;

    private MaintenanceConfigurationFactory $configurationFactory;

    public function __construct(
        FlashBagInterface $flashBag,
        TranslatorInterface $translator,
        ConfigurationFileManager $configurationFileManager,
        MaintenanceConfigurationExporter $maintenanceExporter,
        MaintenanceConfigurationFactory $configurationFactory
    ) {
        $this->flashBag = $flashBag;
        $this->translator = $translator;
        $this->configurationFileManager = $configurationFileManager;
        $this->maintenanceExporter = $maintenanceExporter;
        $this->configurationFactory = $configurationFactory;
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

        return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/maintenanceConfiguration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
