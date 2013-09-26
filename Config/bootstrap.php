<?php

/**
 * Please copy the config below and place it on your /app/Config/bootstrap.php
 * Remember to fill in the fields!
 */

if (Configure::check('NotificationManager.UrbanAirship')) {
    Configure::write('NotificationManager.UrbanAirship.key', '');
    Configure::write('NotificationManager.UrbanAirship.secret', '');
}

App::build([
    'Model' => [
        '/app/Plugins/NotificationManager/Model/'
    ]
]);
    
?>