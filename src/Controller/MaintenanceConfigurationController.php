<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Form\Type\MaintenanceConfigurationType;

final class MaintenanceConfigurationController extends AbstractController
{
    private TranslatorInterface $translator;

    private FlashBagInterface $flashBag;

    private ConfigurationFileManager $configurationFileManager;

    private RepositoryInterface $maintenanceRepository;

    public function __construct(
        FlashBagInterface $flashBag,
        TranslatorInterface $translator,
        ConfigurationFileManager $configurationFileManager,
        RepositoryInterface $maintenanceRepository
    ) {
        $this->flashBag = $flashBag;
        $this->translator = $translator;
        $this->configurationFileManager = $configurationFileManager;
        $this->maintenanceRepository = $maintenanceRepository;
    }

    public function __invoke(Request $request): Response
    {
        $dataFromYaml = $this->configurationFileManager->getDataFromYaml();

        $form = $this->createForm(MaintenanceConfigurationType::class, $dataFromYaml);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($data['enabled']) {
                $this->configurationFileManager->createFile();
                $this->configurationFileManager->saveTemplate($data['customMessage']);

                if (null !== $data['ipAddresses']) {
                    $ipAddresses = $this->configurationFileManager->getIpAddressesArray(explode(',', $data['ipAddresses']));
                    $this->configurationFileManager->saveYamlConfiguration($ipAddresses);
                }

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
