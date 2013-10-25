<?php

/**
 * Please copy the config below and place it on your /app/Config/bootstrap.php
 * Remember to fill in the fields!
 */

Configure::write('NotificationManager.UrbanAirship.key', '');
Configure::write('NotificationManager.UrbanAirship.master', '');

Configure::write('NotificationManager.Twilio.sid', '');
Configure::write('NotificationManager.Twilio.token', '');
Configure::write('NotificationManager.Twilio.number', '');

require APP . 'Plugin' . DS . 'NotificationManager' . DS . 'Lib' . DS . 'Notifier.php';