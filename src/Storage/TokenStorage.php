<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Storage;

use Sylius\Component\Resource\Storage\StorageInterface;

final class TokenStorage
{
    public const MAINTENANCE_COOKIE_NAME = 'synolia_maintenance_cookie';

    private StorageInterface $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function set(string $token): void
    {
        $this->storage->set(self::MAINTENANCE_COOKIE_NAME, $token);
    }

    public function get(): string
    {
        $token = $this->storage->get(self::MAINTENANCE_COOKIE_NAME);
        if (null === $token || !is_string($token)) {
            $token = '';
        }

        return $token;
    }
}
