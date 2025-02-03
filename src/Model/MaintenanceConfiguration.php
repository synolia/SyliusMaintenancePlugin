<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Model;

use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;

class MaintenanceConfiguration
{
    private string $ipAddresses = '';

    private bool $enabled = false;

    private string $customMessage = '';

    private ?\DateTime $startDate = null;

    private ?\DateTime $endDate = null;

    private array $channels = [];

    private string $token;

    private bool $allowBots = false;

    private bool $allowAdmins = false;

    public function __construct()
    {
        $this->token = bin2hex(random_bytes(16));
    }

    public function getIpAddresses(): string
    {
        return $this->ipAddresses;
    }

    public function getArrayIpsAddresses(): array
    {
        $ipAddressesArray = array_map('trim', explode(',', $this->ipAddresses));

        foreach ($ipAddressesArray as $key => $ipAddress) {
            if (false !== filter_var($ipAddress, \FILTER_VALIDATE_IP)) {
                continue;
            }
            unset($ipAddressesArray[$key]);
        }

        return $ipAddressesArray;
    }

    public function setIpAddresses(?string $ipAddresses): self
    {
        if (null === $ipAddresses) {
            $this->ipAddresses = '';

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
            $this->customMessage = '';

            return $this;
        }
        $this->customMessage = $customMessage;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @deprecated
     */
    public function map(?array $dataFromMaintenanceYaml): self
    {
        MaintenanceConfigurationFactory::map($this, $dataFromMaintenanceYaml);

        return $this;
    }

    public function getChannels(): array
    {
        return $this->channels;
    }

    public function setChannels(array $channels): self
    {
        $this->channels = $channels;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function allowBots(): bool
    {
        return $this->allowBots;
    }

    public function setAllowBots(bool $allowBots): self
    {
        $this->allowBots = $allowBots;

        return $this;
    }

    public function isAllowAdmins(): bool
    {
        return $this->allowAdmins;
    }

    public function setAllowAdmins(bool $allowAdmins): self
    {
        $this->allowAdmins = $allowAdmins;

        return $this;
    }
}
