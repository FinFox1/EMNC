<?php
return [
    'routes' => [
        // Room API (compatible with Nextcloud Talk)
        ['name' => 'Room#createRoom', 'url' => '/api/v1/room', 'verb' => 'POST'],
        ['name' => 'Room#getRooms', 'url' => '/api/v1/rooms', 'verb' => 'GET'],
        ['name' => 'Room#getRoom', 'url' => '/api/v1/room/{token}', 'verb' => 'GET'],
        ['name' => 'Room#joinRoom', 'url' => '/api/v1/room/{token}/participants/active', 'verb' => 'POST'],
        ['name' => 'Room#addParticipant', 'url' => '/api/v1/room/{token}/participants', 'verb' => 'POST'],
        ['name' => 'Room#shareFileToRoom', 'url' => '/api/v1/room/{token}/share', 'verb' => 'POST'],
        
        // Chat API (compatible with Nextcloud Talk)
        ['name' => 'Chat#sendMessage', 'url' => '/api/v1/chat/{token}', 'verb' => 'POST'],
        ['name' => 'Chat#getMessages', 'url' => '/api/v1/chat/{token}', 'verb' => 'GET'],
        ['name' => 'Chat#shareFileToChat', 'url' => '/api/v1/chat/{token}/share', 'verb' => 'POST'],
        
        // Admin Settings
        ['name' => 'AdminSettings#saveSettings', 'url' => '/api/v1/admin/settings', 'verb' => 'POST'],
        
        // Integration APIs
        ['name' => 'Integration#getContacts', 'url' => '/api/v1/integration/contacts', 'verb' => 'GET'],
        ['name' => 'Integration#createCalendarEvent', 'url' => '/api/v1/integration/calendar/events', 'verb' => 'POST'],
        ['name' => 'Integration#getFileShare', 'url' => '/api/v1/integration/files/share/{token}', 'verb' => 'GET'],
        
        // Configuration
        ['name' => 'Config#getConfig', 'url' => '/api/v1/config', 'verb' => 'GET'],
    ],
    'ocs' => [
        // OCS API compatibility (for mobile apps)
        ['name' => 'Room#createRoom', 'url' => '/api/v4/room', 'verb' => 'POST'],
        ['name' => 'Room#getRooms', 'url' => '/api/v4/rooms', 'verb' => 'GET'],
        ['name' => 'Chat#sendMessage', 'url' => '/api/v1/chat/{token}', 'verb' => 'POST'],
        ['name' => 'Chat#getMessages', 'url' => '/api/v1/chat/{token}', 'verb' => 'GET'],
    ]
];
