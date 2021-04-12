<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity
 * @ORM\Table("maintenance_configuration")
 */
class MaintenanceConfiguration implements ResourceInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /** @ORM\Column(type="string") */
    private string $ipAddresses = '';

    /** @ORM\Column(type="boolean") */
    private bool $enabled = true;

    /** @ORM\Column(type="text") */
    private string $customMessage = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getIpAddresses(): string
    {
        return $this->ipAddresses;
    }

    public function setIpAddresses(?string $ipAddresses): self
    {
        if (null === $ipAddresses) {
            return $this;
        }
        $this->ipAddresses = $ipAddresses;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getCustomMessage(): string
    {
        return $this->customMessage;
    }

    public function setCustomMessage(?string $customMessage): self
    {
        if (null === $customMessage) {
            return $this;
        }
        $this->customMessage = $customMessage;

        return $this;
    }
}
