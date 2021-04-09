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

    private ConfigurationFileManager $fileManager;

    private RepositoryInterface $maintenanceRepository;

    public function __construct(
        FlashBagInterface $flashBag,
        TranslatorInterface $translator,
        ConfigurationFileManager $fileManager,
        RepositoryInterface $maintenanceRepository
    ) {
        $this->flashBag = $flashBag;
        $this->translator = $translator;
        $this->fileManager = $fileManager;
        $this->maintenanceRepository = $maintenanceRepository;
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(MaintenanceConfigurationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ([] === $data) {
                return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/config.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            if ($data['enabled']) {
                $this->fileManager->createFile(ConfigurationFileManager::MAINTENANCE_FILE);

                if ($this->fileManager->fileExists(ConfigurationFileManager::MAINTENANCE_FILE)) {
                    $this->flashBag->add(
                        'success',
                        $this->translator->trans('maintenance.ui.message_enabled')
                    );
                }

                if (null !== $data['customMessage']) {
                    $this->fileManager->addCustomMessage($data['customMessage']);
                    $this->flashBag->add(
                        'success',
                        $this->translator->trans('maintenance.ui.message_success_message')
                    );
                }

                if (null !== $data['ipAddresses']) {
                    $result = $this->fileManager->putIpsIntoFile(
                        $this->fileManager->convertStringToArray($data['ipAddresses']), ConfigurationFileManager::MAINTENANCE_FILE);

                    if ($result !== ConfigurationFileManager::ADD_IP_SUCCESS) {
                        $this->flashBag->add(
                            'error',
                            $this->translator->trans('maintenance.ui.message_error_ips')
                        );

                        return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/config.html.twig', [
                            'form' => $form->createView(),
                        ]);
                    }

                    $this->flashBag->add(
                        'success',
                        $this->translator->trans('maintenance.ui.message_success_ips')
                    );
                }

                return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/config.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $this->fileManager->deleteFile(ConfigurationFileManager::MAINTENANCE_FILE);
            $this->fileManager->deleteFile(ConfigurationFileManager::MAINTENANCE_TEMPLATE);

            if (!$this->fileManager->fileExists(ConfigurationFileManager::MAINTENANCE_FILE)) {
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
