<?php
namespace OCA\ElementMatrix\Controller;

use OCA\ElementMatrix\Config;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class ConfigController extends Controller {
    private Config $config;

    public function __construct(string $appName, IRequest $request, Config $config) {
        parent::__construct($appName, $request);
        $this->config = $config;
    }

    /**
     * @NoAdminRequired
     * @PublicPage
     */
    public function getConfig(): JSONResponse {
        return new JSONResponse([
            'matrix_server_url' => $this->config->getMatrixServerUrl(),
            'element_url' => $this->config->getElementUrl(),
            'enabled' => $this->config->isEnabled(),
            'files_sharing_enabled' => $this->config->getFilesSharingEnabled(),
            'calendar_integration_enabled' => $this->config->getCalendarIntegrationEnabled(),
            'contacts_integration_enabled' => $this->config->getContactsIntegrationEnabled()
        ]);
    }
}
