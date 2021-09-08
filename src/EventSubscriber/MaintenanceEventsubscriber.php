<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\EventSubscriber;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Twig\Environment;

final class MaintenanceEventsubscriber implements EventSubscriberInterface
{
    private RequestStack $requestStack;

    private FlashBagInterface $flashBag;

    private TranslatorInterface $translator;

    private ParameterBagInterface $params;

    private Environment $twig;

    private MaintenanceConfigurationFactory $configurationFactory;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        Environment $twig,
        MaintenanceConfigurationFactory $configurationFactory,
        FlashBagInterface $flashBag,
        RequestStack $requestStack
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->twig = $twig;
        $this->configurationFactory = $configurationFactory;
        $this->flashBag = $flashBag;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'handle',
        ];
    }

    public function handle(RequestEvent $event): void
    {
        $getRequestUri = $event->getRequest()->getRequestUri();
        /** @var string $adminPrefix */
        $adminPrefix = $this->params->get('sylius_admin.path_name');
        $ipUser = $event->getRequest()->getClientIp();
        $maintenanceConfiguration = $this->configurationFactory->get();

        if (!$maintenanceConfiguration->isEnabled()) {
            return;
        }

        $authorizedIps = $maintenanceConfiguration->getArrayIpsAddresses();
        if (in_array($ipUser, $authorizedIps, true)) {
            return;
        }

        if (false === $this->isActuallyScheduledMaintenance($maintenanceConfiguration) &&
            (null !== $maintenanceConfiguration->getStartDate() ||
             null !== $maintenanceConfiguration->getEndDate())
        ) {
            return;
        }

        if (false !== mb_strpos($getRequestUri, $adminPrefix, 1)) {
            if ($this->requestStack->getMainRequest() === $this->requestStack->getCurrentRequest()) {
                $this->flashBag->add('info', $this->translator->trans('maintenance.ui.message_info_admin'));
            }

            return;
        }

        $responseContent = $this->translator->trans('maintenance.ui.message');

        if ('' !== $maintenanceConfiguration->getCustomMessage()) {
            $responseContent = $this->twig->render('@SynoliaSyliusMaintenancePlugin/maintenance.html.twig', [
                'custom_message' => $maintenanceConfiguration->getCustomMessage(),
            ]);
        }

        $event->setResponse(new Response($responseContent, Response::HTTP_SERVICE_UNAVAILABLE));
    }

    private function isActuallyScheduledMaintenance(MaintenanceConfiguration $maintenanceConfiguration): bool
    {
        $now = new \DateTime();
        $startDate = $maintenanceConfiguration->getStartDate();
        $endDate = $maintenanceConfiguration->getEndDate();
        // Now is between startDate and endDate
        if ($startDate !== null && $endDate !== null && ($now >= $startDate) && ($now <= $endDate)) {
            return true;
        }
        // No enddate provided, now is greater than startDate
        if ($startDate !== null && $endDate === null && ($now >= $startDate)) {
            return true;
        }
        // No startdate provided, now is before than enddate
        if ($endDate !== null && $startDate === null && ($now <= $endDate)) {
            return true;
        }
        // No schedule date
        return false;
    }
}
