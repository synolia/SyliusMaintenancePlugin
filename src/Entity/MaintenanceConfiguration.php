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
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ipAddresses;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $enabled = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpAddresses(): ?string
    {
        return $this->ipAddresses;
    }

    public function setIpAddresses(?string $ipAddresses): self
    {
        $this->ipAddresses = $ipAddresses;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): self
    {
        $this->enabled = (bool) $enabled;

        return $this;
    }

    public function enable(): self
    {
        $this->enabled = true;

        return $this;
    }

    public function disable(): self
    {
        $this->enabled = false;

        return $this;
    }
}
