<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Storage;

use Symfony\Component\HttpFoundation\RequestStack;

final class TokenStorage
{
    public const MAINTENANCE_TOKEN_NAME = 'synolia_maintenance_token';

    public function __construct(private RequestStack $requestStack)
    {
    }

    public function set(string $token): void
    {
        if (method_exists($this->requestStack, 'getMainRequest')) {
            $this->requestStack->getMainRequest()?->getSession()->set(self::MAINTENANCE_TOKEN_NAME, $token);
        }

        /** @TODO Drop after remove Symfony 4.4 compatibility */
        if (method_exists($this->requestStack, 'getMasterRequest')) {
            $this->requestStack->getMasterRequest()?->getSession()->set(self::MAINTENANCE_TOKEN_NAME, $token);
        }
    }

    public function get(): string
    {
        $token = $this->requestStack->getSession()->get(self::MAINTENANCE_TOKEN_NAME);
        if (null === $token || !is_string($token)) {
            $token = '';
        }

        return $token;
    }
}
