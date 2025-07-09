<?php
namespace OCA\ElementMatrix\Service;

use OCP\IDBConnection;
use OCP\IUserManager;
use OCP\Contacts\IManager as ContactsManager;
use OCP\Calendar\IManager as CalendarManager;
use OCP\IGroupManager;
use OCP\Share\IManager as ShareManager;

class NextcloudIntegrationService {
    private IDBConnection $db;
    private IUserManager $userManager;
    private ContactsManager $contactsManager;
    private CalendarManager $calendarManager;
    private IGroupManager $groupManager;
    private ShareManager $shareManager;

    public function __construct(
        IDBConnection $db,
        IUserManager $userManager,
        ContactsManager $contactsManager,
        CalendarManager $calendarManager,
        IGroupManager $groupManager,
        ShareManager $shareManager
    ) {
        $this->db = $db;
        $this->userManager = $userManager;
        $this->contactsManager = $contactsManager;
        $this->calendarManager = $calendarManager;
        $this->groupManager = $groupManager;
        $this->shareManager = $shareManager;
    }

    public function getMatrixUserId(string $nextcloudUserId): string {
        // Convert Nextcloud user ID to Matrix user ID
        return '@' . $nextcloudUserId . ':matrix.example.com';
    }

    public function getNextcloudUserId(string $matrixUserId): string {
        // Convert Matrix user ID to Nextcloud user ID
        return explode(':', explode('@', $matrixUserId)[1])[0];
    }

    public function storeRoomMapping(string $matrixRoomId, string $ownerId, string $roomName, int $type): void {
        $qb = $this->db->getQueryBuilder();
        $qb->insert('elementmatrix_rooms')
            ->values([
                'matrix_room_id' => $qb->createNamedParameter($matrixRoomId),
                'owner_id' => $qb->createNamedParameter($ownerId),
                'room_name' => $qb->createNamedParameter($roomName),
                'room_type' => $qb->createNamedParameter($type),
                'created_at' => $qb->createNamedParameter(time())
            ])
            ->execute();
    }

    public function getUserRooms(string $userId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('elementmatrix_rooms')
            ->where($qb->expr()->eq('owner_id', $qb->createNamedParameter($userId)))
            ->orWhere($qb->expr()->in('matrix_room_id', 
                $qb->createFunction('SELECT room_id FROM elementmatrix_participants WHERE user_id = ' . $qb->createNamedParameter($userId))
            ));
        
        $result = $qb->execute();
        $rooms = [];
        
        while ($row = $result->fetch()) {
            $rooms[] = [
                'id' => $row['matrix_room_id'],
                'name' => $row['room_name'],
                'type' => $row['room_type'],
                'token' => $row['matrix_room_id'],
                'participantType' => $row['owner_id'] === $userId ? 1 : 3,
                'permissions' => $row['owner_id'] === $userId ? 511 : 126,
                'lastActivity' => $row['created_at'],
                'isFavorite' => false,
                'canLeaveConversation' => true,
                'canDeleteConversation' => $row['owner_id'] === $userId
            ];
        }
        
        return $rooms;
    }

    public function getRoom(string $roomId, string $userId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('elementmatrix_rooms')
            ->where($qb->expr()->eq('matrix_room_id', $qb->createNamedParameter($roomId)));
        
        $result = $qb->execute();
        $row = $result->fetch();
        
        if (!$row) {
            throw new \Exception('Room not found');
        }
        
        return [
            'id' => $row['matrix_room_id'],
            'name' => $row['room_name'],
            'type' => $row['room_type'],
            'token' => $row['matrix_room_id'],
            'participantType' => $row['owner_id'] === $userId ? 1 : 3,
            'permissions' => $row['owner_id'] === $userId ? 511 : 126,
            'lastActivity' => $row['created_at'],
            'isFavorite' => false,
            'canLeaveConversation' => true,
            'canDeleteConversation' => $row['owner_id'] === $userId
        ];
    }

    public function addParticipant(string $roomId, string $userId): void {
        $qb = $this->db->getQueryBuilder();
        $qb->insert('elementmatrix_participants')
            ->values([
                'room_id' => $qb->createNamedParameter($roomId),
                'user_id' => $qb->createNamedParameter($userId),
                'joined_at' => $qb->createNamedParameter(time())
            ])
            ->execute();
    }

    public function getDisplayName(string $matrixUserId): string {
        $nextcloudUserId = $this->getNextcloudUserId($matrixUserId);
        $user = $this->userManager->get($nextcloudUserId);
        return $user ? $user->getDisplayName() : $nextcloudUserId;
    }

    public function getUserContacts(string $userId): array {
        $contacts = $this->contactsManager->search('', ['FN', 'EMAIL'], [
            'types' => true,
            'escape_like_param' => false,
            'limit' => 500,
            'offset' => 0
        ]);
        
        $result = [];
        foreach ($contacts as $contact) {
            $result[] = [
                'id' => $contact['UID'],
                'name' => $contact['FN'],
                'email' => $contact['EMAIL'][0] ?? '',
                'canInvite' => true
            ];
        }
        
        return $result;
    }

    public function createCalendarEvent(string $roomId, array $eventData): void {
        $calendars = $this->calendarManager->getCalendarsForPrincipal('principals/users/' . $eventData['userId']);
        
        if (empty($calendars)) {
            return;
        }
        
        $calendar = $calendars[0];
        $calendar->createFromString($eventData['vcalendar']);
    }
}
