<?php
namespace OCA\ElementMatrix\Controller;

use OCA\ElementMatrix\Service\MatrixApiService;
use OCA\ElementMatrix\Service\NextcloudIntegrationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUserSession;

class ChatController extends Controller {
    private MatrixApiService $matrixApi;
    private NextcloudIntegrationService $integrationService;
    private IUserSession $userSession;

    public function __construct(
        string $appName,
        IRequest $request,
        MatrixApiService $matrixApi,
        NextcloudIntegrationService $integrationService,
        IUserSession $userSession
    ) {
        parent::__construct($appName, $request);
        $this->matrixApi = $matrixApi;
        $this->integrationService = $integrationService;
        $this->userSession = $userSession;
    }

    /**
     * @NoAdminRequired
     */
    public function sendMessage(string $token, string $message): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            $result = $this->matrixApi->sendMessage($token, $message);
            
            return new JSONResponse([
                'id' => $result['event_id'],
                'token' => $token,
                'actorType' => 'users',
                'actorId' => $user->getUID(),
                'actorDisplayName' => $user->getDisplayName(),
                'timestamp' => time(),
                'message' => $message,
                'messageType' => 'comment',
                'isReplyable' => true,
                'referenceId' => $result['event_id']
            ]);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function getMessages(string $token, int $lookIntoFuture = 0, int $limit = 20, int $lastKnownMessageId = 0): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            $result = $this->matrixApi->getMessages($token, null, $limit);
            
            $messages = [];
            foreach ($result['chunk'] as $event) {
                if ($event['type'] === 'm.room.message') {
                    $messages[] = [
                        'id' => $event['event_id'],
                        'token' => $token,
                        'actorType' => 'users',
                        'actorId' => $this->integrationService->getNextcloudUserId($event['sender']),
                        'actorDisplayName' => $this->integrationService->getDisplayName($event['sender']),
                        'timestamp' => intval($event['origin_server_ts'] / 1000),
                        'message' => $event['content']['body'],
                        'messageType' => 'comment',
                        'isReplyable' => true,
                        'referenceId' => $event['event_id']
                    ];
                }
            }
            
            return new JSONResponse([
                'messages' => $messages,
                'headers' => [
                    'X-Chat-Last-Common-Read' => $lastKnownMessageId,
                    'X-Chat-Last-Given' => count($messages) > 0 ? $messages[0]['id'] : 0
                ]
            ]);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function shareFileToChat(string $token, string $path): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            // Get file from user's files
            $userFolder = $this->rootFolder->getUserFolder($user->getUID());
            $file = $userFolder->get($path);
            
            // Upload to Matrix
            $fileContent = $file->getContent();
            $mimeType = $file->getMimeType();
            $fileName = $file->getName();
            
            $uploadResult = $this->matrixApi->uploadFile($fileName, $mimeType, $fileContent);
            
            // Send file message
            $message = "File: " . $fileName;
            $result = $this->matrixApi->sendMessage($token, $message, 'm.file');
            
            return new JSONResponse([
                'id' => $result['event_id'],
                'token' => $token,
                'actorType' => 'users',
                'actorId' => $user->getUID(),
                'actorDisplayName' => $user->getDisplayName(),
                'timestamp' => time(),
                'message' => $message,
                'messageType' => 'comment',
                'isReplyable' => true,
                'referenceId' => $result['event_id']
            ]);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }
}
