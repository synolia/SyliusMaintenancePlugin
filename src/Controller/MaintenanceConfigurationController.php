<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Exception\DumpException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Entity\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Form\Type\MaintenanceConfigurationType;

final class MaintenanceConfigurationController extends AbstractController
{
    private const MAINTENANCE_FILE = 'maintenance.yaml';

    private TranslatorInterface $translator;

    private FlashBagInterface $flashBag;

    private Filesystem $filesystem;

    private KernelInterface $kernel;

    public function __construct(
        FlashBagInterface $flashBag,
        TranslatorInterface $translator,
        Filesystem $filesystem,
        KernelInterface $kernel
    ) {
        $this->flashBag = $flashBag;
        $this->translator = $translator;
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(MaintenanceConfigurationType::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (0 == \count($data)) {
                return $this->redirectToRoute('sylius_admin_maintenance_configuration', [
                    'form' => $form->createView(),
                ]);
            }

            if (true === $data['enabled']) {
                $this->createFile(self::MAINTENANCE_FILE);

                if (null !== $data['ipAddresses']) {
                    $this->putIpsIntoFile($data['ipAddresses']);
                }

                if ($this->fileExists(self::MAINTENANCE_FILE)) {
                    $this->flashBag->add(
                        'success',
                        $this->translator->trans('maintenance.ui.message_enabled')
                    );
                }

                return $this->redirectToRoute('sylius_admin_maintenance_configuration', [
                    'form' => $form->createView(),
                ]);
            }

            $this->deleteFile(self::MAINTENANCE_FILE);

            if (!$this->fileExists(self::MAINTENANCE_FILE)) {
                $this->flashBag->add(
                    'success',
                    $this->translator->trans('maintenance.ui.message_disabled')
                );
            }

            $this->saveConfiguration($data);
        }

        return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/config.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function createFile(string $filename): void
    {
        $this->deleteFile($filename);
        $this->filesystem->touch($this->getPathtoFile($filename));
    }

    private function deleteFile(string $filename): void
    {
        if (!$this->fileExists($filename)) {
            return;
        }
        $this->filesystem->remove($this->getPathtoFile($filename));
    }

    private function fileExists(string $filename): bool
    {
        if (!$this->filesystem->exists($this->getPathtoFile($filename))) {
            return false;
        }

        return true;
    }

    private function putIpsIntoFile(string $ipAddresses): void
    {
        $ipAddressesArrayBeforeTrim = explode(',', $ipAddresses);

        $ipAddressesArray = $this->trimSpaceOfArrayValues($ipAddressesArrayBeforeTrim);

        foreach ($ipAddressesArray as $key => $ipAddress) {
            if ($this->isValidIp($ipAddress)) {
                continue;
            }
            unset($ipAddressesArray[$key]);
        }

        if ($this->fileExists(self::MAINTENANCE_FILE) && \count($ipAddressesArray) > 0) {
            $ipsArray = [
                'ips' => $ipAddressesArray,
            ];

            try {
                $yaml = Yaml::dump($ipsArray);
            } catch (DumpException $exception) {
                throw new DumpException('Unable to dump the YAML. ' . $exception->getMessage());
            }
            file_put_contents($this->getPathtoFile(self::MAINTENANCE_FILE), $yaml);
        }
    }

    private function getPathtoFile(string $filename): string
    {
        $projectRootPath = $this->kernel->getProjectDir();

        return $projectRootPath . '/' . $filename;
    }

    private function isValidIp(string $ipAddress): bool
    {
        if (false === filter_var($ipAddress, \FILTER_VALIDATE_IP)) {
            return false;
        }

        return true;
    }

    private function trimSpaceOfArrayValues(array $array): array
    {
        $result = [];
        foreach ($array as $value) {
            $result[] = trim($value);
        }

        return $result;
    }

    private function saveConfiguration(array $formData): void
    {
        $maintenanceConfig = new MaintenanceConfiguration();
        $maintenanceConfig->setEnabled($formData['enabled']);
        $maintenanceConfig->setIpAddresses($formData['ipAddresses']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($maintenanceConfig);
        $entityManager->flush();
    }
}
