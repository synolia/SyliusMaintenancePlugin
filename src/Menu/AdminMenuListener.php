<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Menu;

use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'sylius.menu.admin.main', method: 'addAdminMenuItems')]
final class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        /** @var ItemInterface $newSubmenu */
        $newSubmenu = $menu->getChild('configuration');

        $newSubmenu
            ->addChild('sylius_admin_maintenance_configuration', [
                'route' => 'sylius_admin_maintenance_configuration',
            ])
            ->setAttribute('type', 'link')
            ->setLabel('maintenance.ui.title')
            ->setLabelAttribute('icon', 'toggle off')
        ;
    }
}
