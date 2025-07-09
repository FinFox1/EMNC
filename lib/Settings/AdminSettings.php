<?php
namespace OCA\ElementMatrix\Settings;

use OCA\ElementMatrix\Config;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IInitialStateService;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {
    private Config $config;
    private IInitialStateService $initialStateService;

    public function __construct(Config $config, IInitialStateService $initialStateService) {
        $this->config = $config;
        $this->initialStateService = $initialStateService;
    }

    public function getForm(): TemplateResponse {
        $this->initialStateService->provideInitialState('elementmatrix', 'admin-settings', [
            'matrix_server_url' => $this->config->getMatrixServerUrl(),
            'element_url' => $this->config->getElementUrl(),
            'enabled' => $this->config->isEnabled(),
            'files_sharing_enabled' => $this->config->getFilesSharingEnabled(),
            'calendar_integration_enabled' => $this->config->getCalendarIntegrationEnabled(),
            'contacts_integration_enabled' => $this->config->getContactsIntegrationEnabled(),
        ]);

        return new TemplateResponse('elementmatrix', 'admin-settings');
    }

    public function getSection(): string {
        return 'elementmatrix';
    }

    public function getPriority(): int {
        return 50;
    }
}
