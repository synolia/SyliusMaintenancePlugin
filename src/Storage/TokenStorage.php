<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Storage;

use Symfony\Component\HttpFoundation\RequestStack;

final readonly class TokenStorage
{
    public const MAINTENANCE_TOKEN_NAME = 'synolia_maintenance_token';

    public function __construct(private RequestStack $requestStack)
    {
    }

    public function set(string $token): void
    {
        $this->requestStack->getMainRequest()?->getSession()->set(self::MAINTENANCE_TOKEN_NAME, $token);
    }

    public function get(): string
    {
        $token = $this->requestStack->getSession()->get(self::MAINTENANCE_TOKEN_NAME);
        if (!is_string($token)) {
            $token = '';
        }

        return $token;
    }
}
