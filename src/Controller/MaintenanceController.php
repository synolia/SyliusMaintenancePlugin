<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

final class MaintenanceController extends AbstractController
{
    public function __invoke(): JsonResponse
    {
        return $this->json('site en maintenance !!!');
    }
}
