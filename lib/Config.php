<?php
namespace OCA\ElementMatrix;

use OCP\IConfig;
use OCP\ILogger;

class Config {
    private IConfig $config;
    private ILogger $logger;

    public function __construct(IConfig $config, ILogger $logger) {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function getMatrixServerUrl(): string {
        return $this->config->getAppValue('elementmatrix', 'matrix_server_url', 'https://matrix.example.com');
    }

    public function getMatrixAccessToken(): string {
        return $this->config->getAppValue('elementmatrix', 'matrix_access_token', '');
    }

    public function getElementUrl(): string {
        return $this->config->getAppValue('elementmatrix', 'element_url', 'https://element.example.com');
    }

    public function isEnabled(): bool {
        return $this->config->getAppValue('elementmatrix', 'enabled', 'false') === 'true';
    }

    public function getDefaultPowerLevel(): int {
        return (int)$this->config->getAppValue('elementmatrix', 'default_power_level', '0');
    }

    public function getFilesSharingEnabled(): bool {
        return $this->config->getAppValue('elementmatrix', 'files_sharing_enabled', 'true') === 'true';
    }

    public function getCalendarIntegrationEnabled(): bool {
        return $this->config->getAppValue('elementmatrix', 'calendar_integration_enabled', 'true') === 'true';
    }

    public function getContactsIntegrationEnabled(): bool {
        return $this->config->getAppValue('elementmatrix', 'contacts_integration_enabled', 'true') === 'true';
    }
}
