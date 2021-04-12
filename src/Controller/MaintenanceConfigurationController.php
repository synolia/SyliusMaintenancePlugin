<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Exporter\MaintenanceConfigurationExporter;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Form\Type\MaintenanceConfigurationType;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;

final class MaintenanceConfigurationController extends AbstractController
{
    private TranslatorInterface $translator;

    private FlashBagInterface $flashBag;

    private ConfigurationFileManager $configurationFileManager;

    private MaintenanceConfigurationExporter $maintenanceExporter;

    public function __construct(
        FlashBagInterface $flashBag,
        TranslatorInterface $translator,
        ConfigurationFileManager $configurationFileManager,
        MaintenanceConfigurationExporter $maintenanceExporter
    ) {
        $this->flashBag = $flashBag;
        $this->translator = $translator;
        $this->configurationFileManager = $configurationFileManager;
        $this->maintenanceExporter = $maintenanceExporter;
    }

    public function __invoke(Request $request): Response
    {
        $maintenanceConfiguration = new MaintenanceConfiguration();

        $form = $this->createForm(MaintenanceConfigurationType::class, $maintenanceConfiguration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($maintenanceConfiguration->isEnabled()) {
                $this->configurationFileManager->createFile();

                $this->maintenanceExporter->export($maintenanceConfiguration);

                $this->flashBag->add('success', $this->translator->trans('maintenance.ui.message_enabled'));

                return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/config.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $this->configurationFileManager->deleteFile();

            if (!$this->configurationFileManager->fileExists(ConfigurationFileManager::MAINTENANCE_FILE)) {
                $this->flashBag->add(
                    'success',
                    $this->translator->trans('maintenance.ui.message_disabled')
                );
            }
        }

        return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/config.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
