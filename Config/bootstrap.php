<?php

/**
 * Please copy the config below and place it on your /app/Config/bootstrap.php
 * Remember to fill in the fields!
 */

if (!Configure::check('NotificationManager.UrbanAirship')) {
    Configure::write('NotificationManager.UrbanAirship.key', '');
    Configure::write('NotificationManager.UrbanAirship.master', '');
}

if (!Configure::check('NotificationManager.Twilio')) {
    Configure::write('NotificationManager.Twilio.sid', '');
    Configure::write('NotificationManager.Twilio.token', '');
    Configure::write('NotificationManager.Twilio.number', '');
}