<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Model;

class MaintenanceConfiguration
{
    private string $ipAddresses = '';

    private bool $enabled = false;

    private string $customMessage = '';

    private ?\DateTime $startDate;

    private ?\DateTime $endDate;

    public function __construct()
    {
        $this->startDate = null;
        $this->endDate = null;
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

    public function map(?array $dataFromMaintenanceYaml): self
    {
        if (null === $dataFromMaintenanceYaml) {
            return $this;
        }
        if (array_key_exists('ips', $dataFromMaintenanceYaml)) {
            $this->setIpAddresses(implode(',', $dataFromMaintenanceYaml['ips']));
        }
        if (array_key_exists('scheduler', $dataFromMaintenanceYaml)) {
            $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', $dataFromMaintenanceYaml['scheduler']['start_date'] ?? '');
            $this->setStartDate(false === $startDate ? null : $startDate);
            $endDate = \DateTime::createFromFormat('Y-m-d H:i:s', $dataFromMaintenanceYaml['scheduler']['end_date'] ?? '');
            $this->setEndDate(false === $endDate ? null : $endDate);
        }
        if (array_key_exists('custom_message', $dataFromMaintenanceYaml)) {
            $this->setCustomMessage($dataFromMaintenanceYaml['custom_message'] ?? '');
        }

        return $this;
    }
}
