<?php
namespace OCA\ElementMatrix\Controller;

use OCA\ElementMatrix\Config;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;

class AdminSettingsController extends Controller {
    private IConfig $config;

    public function __construct(string $appName, IRequest $request, IConfig $config) {
        parent::__construct($appName, $request);
        $this->config = $config;
    }

    /**
     * @AuthorizedAdminSetting(settings=OCA\ElementMatrix\Settings\AdminSettings)
     */
    public function saveSettings(
        string $matrix_server_url,
        string $matrix_access_token,
        string $element_url,
        bool $enabled,
        bool $files_sharing_enabled,
        bool $calendar_integration_enabled,
        bool $contacts_integration_enabled
    ): JSONResponse {
        $this->config->setAppValue('elementmatrix', 'matrix_server_url', $matrix_server_url);
        $this->config->setAppValue('elementmatrix', 'matrix_access_token', $matrix_access_token);
        $this->config->setAppValue('elementmatrix', 'element_url', $element_url);
        $this->config->setAppValue('elementmatrix', 'enabled', $enabled ? 'true' : 'false');
        $this->config->setAppValue('elementmatrix', 'files_sharing_enabled', $files_sharing_enabled ? 'true' : 'false');
        $this->config->setAppValue('elementmatrix', 'calendar_integration_enabled', $calendar_integration_enabled ? 'true' : 'false');
        $this->config->setAppValue('elementmatrix', 'contacts_integration_enabled', $contacts_integration_enabled ? 'true' : 'false');

        return new JSONResponse(['status' => 'success']);
    }
}
