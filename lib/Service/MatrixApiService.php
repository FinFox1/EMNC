<?php
namespace OCA\ElementMatrix\Service;

use OCA\ElementMatrix\Config;
use OCP\Http\Client\IClientService;
use OCP\ILogger;
use Exception;

class MatrixApiService {
    private Config $config;
    private IClientService $clientService;
    private ILogger $logger;

    public function __construct(Config $config, IClientService $clientService, ILogger $logger) {
        $this->config = $config;
        $this->clientService = $clientService;
        $this->logger = $logger;
    }

    public function createRoom(string $name, bool $isPublic = false, array $invite = []): array {
        $client = $this->clientService->newClient();
        $url = $this->config->getMatrixServerUrl() . '/_matrix/client/v3/createRoom';
        
        $data = [
            'name' => $name,
            'visibility' => $isPublic ? 'public' : 'private',
            'preset' => $isPublic ? 'public_chat' : 'private_chat',
            'invite' => $invite,
            'room_version' => '9'
        ];

        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->getMatrixAccessToken(),
                'Content-Type' => 'application/json'
            ],
            'json' => $data
        ]);

        return json_decode($response->getBody(), true);
    }

    public function joinRoom(string $roomId, string $userId): array {
        $client = $this->clientService->newClient();
        $url = $this->config->getMatrixServerUrl() . '/_matrix/client/v3/rooms/' . $roomId . '/join';
        
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->getMatrixAccessToken(),
                'Content-Type' => 'application/json'
            ],
            'json' => ['user_id' => $userId]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function sendMessage(string $roomId, string $message, string $messageType = 'm.text'): array {
        $client = $this->clientService->newClient();
        $txnId = uniqid();
        $url = $this->config->getMatrixServerUrl() . '/_matrix/client/v3/rooms/' . $roomId . '/send/m.room.message/' . $txnId;
        
        $data = [
            'msgtype' => $messageType,
            'body' => $message
        ];

        $response = $client->put($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->getMatrixAccessToken(),
                'Content-Type' => 'application/json'
            ],
            'json' => $data
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getMessages(string $roomId, string $from = null, int $limit = 20): array {
        $client = $this->clientService->newClient();
        $url = $this->config->getMatrixServerUrl() . '/_matrix/client/v3/rooms/' . $roomId . '/messages';
        
        $params = [
            'dir' => 'b',
            'limit' => $limit
        ];
        
        if ($from) {
            $params['from'] = $from;
        }

        $response = $client->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->getMatrixAccessToken()
            ],
            'query' => $params
        ]);

        return json_decode($response->getBody(), true);
    }

    public function inviteUser(string $roomId, string $userId): array {
        $client = $this->clientService->newClient();
        $url = $this->config->getMatrixServerUrl() . '/_matrix/client/v3/rooms/' . $roomId . '/invite';
        
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->getMatrixAccessToken(),
                'Content-Type' => 'application/json'
            ],
            'json' => ['user_id' => $userId]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function uploadFile(string $fileName, string $mimeType, $fileContent): array {
        $client = $this->clientService->newClient();
        $url = $this->config->getMatrixServerUrl() . '/_matrix/media/v3/upload';
        
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->getMatrixAccessToken(),
                'Content-Type' => $mimeType
            ],
            'body' => $fileContent
        ]);

        return json_decode($response->getBody(), true);
    }
}
