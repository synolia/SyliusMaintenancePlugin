<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Exporter\MaintenanceConfigurationExporter;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Form\Type\MaintenanceConfigurationType;

#[AsController]
final class MaintenanceConfigurationController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly MaintenanceConfigurationExporter $maintenanceExporter,
        private readonly MaintenanceConfigurationFactory $configurationFactory,
        private readonly CacheInterface $synoliaMaintenanceCache,
    ) {
    }

    #[Route('/maintenance/configuration', name: 'sylius_admin_maintenance_configuration', defaults: ['_sylius' => ['permission' => true, 'section' => 'admin', 'alias' => 'plugin_synolia_maintenance']], methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $maintenanceConfiguration = $this->configurationFactory->get();
        $form = $this->createForm(MaintenanceConfigurationType::class, $maintenanceConfiguration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getFlashBag();

            if ($maintenanceConfiguration->getEndDate() instanceof \DateTime && $maintenanceConfiguration->getEndDate() < (new \DateTime())) {
                $maintenanceConfiguration->setEnabled(false);
                $flashBag->add('error', $this->translator->trans('maintenance.ui.message_end_date_in_the_past'));
            }

            $this->maintenanceExporter->export($maintenanceConfiguration);
            $message = 'maintenance.ui.message_disabled';
            if ($maintenanceConfiguration->isEnabled()) {
                $message = 'maintenance.ui.message_enabled';
            }

            $this->synoliaMaintenanceCache->delete(ConfigurationFileManager::MAINTENANCE_CACHE_KEY);

            $flashBag->add('success', $this->translator->trans($message));
        }

        return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/maintenanceConfiguration.html.twig', [
            'maintenanceConfiguration' => $maintenanceConfiguration,
            'form' => $form->createView(),
        ]);
    }
}
