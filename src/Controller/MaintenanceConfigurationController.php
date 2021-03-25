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
                $this->redirectToRoute('sylius_admin_maintenance_configuration');
            }

            if ($data['enabled'] === true) {
                $this->createFile();

                if (null !== $data['ip']) {
                    $this->putIpsIntoFile($data['ip']);
                }

                if ($this->fileExists()) {
                    $this->flashBag->add(
                        'success',
                        $this->translator->trans('maintenance.ui.message_enabled')
                    );
                }
            } elseif ($data['enabled'] === false) {
                $this->deleteFile();
                if (!$this->fileExists()) {
                    $this->flashBag->add(
                        'success',
                        $this->translator->trans('maintenance.ui.message_disabled')
                    );
                }
            }
        }

        return $this->render('@SynoliaSyliusMaintenancePlugin/Admin/config.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function createFile(): void
    {
        $this->deleteFile();
        $this->filesystem->touch($this->getPathtoFile());
    }

    private function deleteFile(): void
    {
        if ($this->fileExists()) {
            $this->filesystem->remove($this->getPathtoFile());
        }
    }

    private function fileExists(): bool
    {
        if ($this->filesystem->exists($this->getPathtoFile())) {
            return true;
        }

        return false;
    }

    private function putIpsIntoFile(string $ipAddresses): void
    {
        $ipAddressesArray = explode(',', $ipAddresses);

        foreach ($ipAddressesArray as $key => $ipAddress) {
            if (!$this->isValidIp($ipAddress)) {
                unset($ipAddressesArray[$key]);
            }
        }

        if ($this->fileExists() && \count($ipAddressesArray) > 0) {
            $ipsArray = [
                'ips' => $ipAddressesArray,
            ];

            try {
                $yaml = Yaml::dump($ipsArray);
            } catch (DumpException $exception) {
                throw new DumpException('Unable to dump the YAML. ' . $exception->getMessage());
            }
            file_put_contents($this->getPathtoFile(), $yaml);
        }
    }

    private function getPathtoFile(): string
    {
        $projectRootPath = $this->kernel->getProjectDir();

        return  $projectRootPath . '/' . self::MAINTENANCE_FILE;
    }

    private function isValidIp(string $ipAddress): bool
    {
        if (filter_var($ipAddress, \FILTER_VALIDATE_IP) !== false) {
            return true;
        }

        return false;
    }
}
