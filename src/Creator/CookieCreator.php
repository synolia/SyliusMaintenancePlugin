<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Creator;

use Symfony\Component\HttpFoundation\Cookie;

final class CookieCreator
{
    public const MAINTENANCE_COOKIE_NAME = 'synolia_maintenance_cookie';

    private const MAINTENANCE_COOKIE_EXPIRE = 30 * 24 * 60 * 60; // 1 month

    private string $domainForCookie;

    public function __construct(string $domainForCookie)
    {
        $this->domainForCookie = $domainForCookie;
    }

    public function create(string $token): Cookie
    {
        return Cookie::create(
            self::MAINTENANCE_COOKIE_NAME,
            $token,
            self::MAINTENANCE_COOKIE_EXPIRE,
            '/',
            $this->domainForCookie,
        );
    }
}
