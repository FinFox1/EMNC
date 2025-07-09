<?php
namespace OCA\ElementMatrix\Controller;

use OCA\ElementMatrix\Service\NextcloudIntegrationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUserSession;

class IntegrationController extends Controller {
    private NextcloudIntegrationService $integrationService;
    private IUserSession $userSession;

    public function __construct(
        string $appName,
        IRequest $request,
        NextcloudIntegrationService $integrationService,
        IUserSession $userSession
    ) {
        parent::__construct($appName, $request);
        $this->integrationService = $integrationService;
        $this->userSession = $userSession;
    }

    /**
     * @NoAdminRequired
     */
    public function getContacts(): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            $contacts = $this->integrationService->getUserContacts($user->getUID());
            return new JSONResponse($contacts);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function createCalendarEvent(string $roomId, string $title, int $startTime, int $endTime): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            $eventData = [
                'userId' => $user->getUID(),
                'vcalendar' => $this->generateVCalendar($title, $startTime, $endTime, $roomId)
            ];
            
            $this->integrationService->createCalendarEvent($roomId, $eventData);
            return new JSONResponse(['status' => 'created']);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function getFileShare(string $token): JSONResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            return new JSONResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            $share = $this->shareManager->getShareByToken($token);
            $file = $share->getNode();
            
            return new JSONResponse([
                'id' => $file->getId(),
                'name' => $file->getName(),
                'path' => $file->getPath(),
                'size' => $file->getSize(),
                'mimeType' => $file->getMimeType(),
                'downloadUrl' => $this->generateUrl('files.view.download', ['token' => $token])
            ]);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 404);
        }
    }

    private function generateVCalendar(string $title, int $startTime, int $endTime, string $roomId): string {
        $start = new \DateTime('@' . $startTime);
        $end = new \DateTime('@' . $endTime);
        
        return "BEGIN:VCALENDAR\r\n" .
               "VERSION:2.0\r\n" .
               "PRODID:-//ElementMatrix//ElementMatrix//EN\r\n" .
               "BEGIN:VEVENT\r\n" .
               "UID:" . uniqid() . "@elementmatrix\r\n" .
               "DTSTART:" . $start->format('Ymd\THis\Z') . "\r\n" .
               "DTEND:" . $end->format('Ymd\THis\Z') . "\r\n" .
               "SUMMARY:" . $title . "\r\n" .
               "DESCRIPTION:Matrix room: " . $roomId . "\r\n" .
               "END:VEVENT\r\n" .
               "END:VCALENDAR\r\n";
    }
}
