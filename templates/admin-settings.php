<?php
script('elementmatrix', 'admin-settings');
style('elementmatrix', 'admin-settings');
?>

<div id="elementmatrix-admin-settings" class="section">
    <h2><?php p($l->t('ElementMatrix Settings')); ?></h2>
    
    <div class="form-group">
        <label for="matrix-server-url"><?php p($l->t('Matrix Server URL')); ?></label>
        <input type="url" id="matrix-server-url" name="matrix_server_url" 
               placeholder="https://matrix.example.com" />
        <p class="info"><?php p($l->t('URL of your Matrix homeserver')); ?></p>
    </div>

    <div class="form-group">
        <label for="matrix-access-token"><?php p($l->t('Matrix Access Token')); ?></label>
        <input type="password" id="matrix-access-token" name="matrix_access_token" 
               placeholder="<?php p($l->t('Access token for Matrix API')); ?>" />
        <p class="info"><?php p($l->t('Administrative access token for Matrix operations')); ?></p>
    </div>

    <div class="form-group">
        <label for="element-url"><?php p($l->t('Element Web URL')); ?></label>
        <input type="url" id="element-url" name="element_url" 
               placeholder="https://element.example.com" />
        <p class="info"><?php p($l->t('URL of your Element Web installation')); ?></p>
    </div>

    <div class="form-group">
        <input type="checkbox" id="enabled" name="enabled" />
        <label for="enabled"><?php p($l->t('Enable ElementMatrix')); ?></label>
    </div>

    <div class="form-group">
        <input type="checkbox" id="files-sharing-enabled" name="files_sharing_enabled" />
        <label for="files-sharing-enabled"><?php p($l->t('Enable Files Sharing Integration')); ?></label>
    </div>

    <div class="form-group">
        <input type="checkbox" id="calendar-integration-enabled" name="calendar_integration_enabled" />
        <label for="calendar-integration-enabled"><?php p($l->t('Enable Calendar Integration')); ?></label>
    </div>

    <div class="form-group">
        <input type="checkbox" id="contacts-integration-enabled" name="contacts_integration_enabled" />
        <label for="contacts-integration-enabled"><?php p($l->t('Enable Contacts Integration')); ?></label>
    </div>

    <button id="save-settings" class="primary"><?php p($l->t('Save Settings')); ?></button>
</div>
