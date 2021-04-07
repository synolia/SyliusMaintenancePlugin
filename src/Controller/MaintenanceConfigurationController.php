<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Entity\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Form\Type\MaintenanceConfigurationType;

final class MaintenanceConfigurationController extends AbstractController
{
    private const MAINTENANCE_FILE = 'maintenance.yaml';

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
        /** @var MaintenanceConfiguration|null $maintenanceConfiguration */
        $maintenanceConfiguration = $this->maintenanceRepository->findOneBy([]);

        if (null === $maintenanceConfiguration) {
            $maintenanceConfiguration = $this->saveConfiguration(null);
        }

        $form = $this->createForm(MaintenanceConfigurationType::class, $maintenanceConfiguration);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ([] === (array) $data) {
                return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/config.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            if ($data->isEnabled()) {
                $this->fileManager->createFile(self::MAINTENANCE_FILE);

                if ($this->fileManager->fileExists(self::MAINTENANCE_FILE)) {
                    $this->flashBag->add(
                        'success',
                        $this->translator->trans('maintenance.ui.message_enabled')
                    );
                }

                if (null !== $data->getIpAddresses()) {
                    $result = $this->fileManager->putIpsIntoFile(
                        $this->fileManager->convertStringToArray($data->getIpAddresses()), self::MAINTENANCE_FILE
                    );

                    if ($result !== ConfigurationFileManager::ADD_IP_SUCCESS) {
                        $this->flashBag->add(
                            'error',
                            $this->translator->trans('maintenance.ui.message_error_ips')
                        );

                        return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/config.html.twig', [
                            'form' => $form->createView(),
                        ]);
                    }

                    $this->removeConfiguration($maintenanceConfiguration);
                    $this->saveConfiguration($data);

                    $this->flashBag->add(
                        'success',
                        $this->translator->trans('maintenance.ui.message_success_ips')
                    );
                }

                return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/config.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $this->fileManager->deleteFile(self::MAINTENANCE_FILE);

            if (!$this->fileManager->fileExists(self::MAINTENANCE_FILE)) {
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

    private function saveConfiguration(?MaintenanceConfiguration $formData): MaintenanceConfiguration
    {
        $maintenanceConfig = new MaintenanceConfiguration();
        $maintenanceConfig->setEnabled($formData !== null ? $formData->isEnabled() : true);
        $maintenanceConfig->setIpAddresses($formData !== null ? $formData->getIpAddresses() : '');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($maintenanceConfig);
        $entityManager->flush();

        return $maintenanceConfig;
    }

    private function removeConfiguration(MaintenanceConfiguration $config): void
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($config);
        $entityManager->flush();
    }
}
