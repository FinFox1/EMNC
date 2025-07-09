<?php
namespace OCA\ElementMatrix\Controller;

use OCA\ElementMatrix\Service\MatrixApiService;
use OCA\ElementMatrix\Service\NextcloudIntegrationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Files\IRootFolder;
use OCP\Share\IManager as ShareManager;

class RoomController extends Controller {
    private MatrixApiService $matrixApi;
    private NextcloudIntegrationService $integrationService;
    private IUserSession $userSession;
    private IRootFolder $rootFolder;
    private ShareManager $shareManager;

    public function __construct(
        string $appName,
        IRequest $request,
        MatrixApiService $matrixApi,
        NextcloudIntegrationService $integrationService,
        IUserSession $userSession,
        IRootFolder $rootFolder,
        ShareManager $shareManager
    ) {
        parent::__construct($appName, $request);
        $this->matrixApi = $matrixApi;
        $this->integrationService = $integrationService;
        $this->userSession = $userSession;
        $this->rootFolder = $rootFolder;
        $this->shareManager = $shareManager;
    }

    /**
     * @NoAdminRequired
     */
    public function createRoom(string $roomName, int $type = 2, string $password = ''): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            $isPublic = $type === 3; // Public room
            $result = $this->matrixApi->createRoom($roomName, $isPublic);
            
            // Store room mapping in database
            $this->integrationService->storeRoomMapping($result['room_id'], $user->getUID(), $roomName, $type);
            
            return new JSONResponse([
                'id' => $result['room_id'],
                'name' => $roomName,
                'type' => $type,
                'token' => $result['room_id'], // Matrix room ID as token
                'participantType' => 1, // Owner
                'permissions' => 511, // All permissions
                'lastActivity' => time(),
                'isFavorite' => false,
                'canLeaveConversation' => true,
                'canDeleteConversation' => true
            ]);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function getRooms(): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            $rooms = $this->integrationService->getUserRooms($user->getUID());
            return new JSONResponse($rooms);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function getRoom(string $token): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            $room = $this->integrationService->getRoom($token, $user->getUID());
            return new JSONResponse($room);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function joinRoom(string $token): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            $matrixUserId = $this->integrationService->getMatrixUserId($user->getUID());
            $result = $this->matrixApi->joinRoom($token, $matrixUserId);
            
            // Update participant status
            $this->integrationService->addParticipant($token, $user->getUID());
            
            return new JSONResponse(['status' => 'joined']);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function addParticipant(string $token, string $newParticipant): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            $matrixUserId = $this->integrationService->getMatrixUserId($newParticipant);
            $result = $this->matrixApi->inviteUser($token, $matrixUserId);
            
            $this->integrationService->addParticipant($token, $newParticipant);
            
            return new JSONResponse(['status' => 'invited']);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function shareFileToRoom(string $token, string $shareToken): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            // Get file from share token
            $share = $this->shareManager->getShareByToken($shareToken);
            $file = $share->getNode();
            
            // Upload file to Matrix
            $fileContent = $file->getContent();
            $mimeType = $file->getMimeType();
            $fileName = $file->getName();
            
            $uploadResult = $this->matrixApi->uploadFile($fileName, $mimeType, $fileContent);
            
            // Send file message
            $message = "File shared: " . $fileName;
            $this->matrixApi->sendMessage($token, $message, 'm.file');
            
            return new JSONResponse(['status' => 'shared']);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }
}
