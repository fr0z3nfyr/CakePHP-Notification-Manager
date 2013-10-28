<?php

/**
 * Please copy the config below and place it on your /app/Config/bootstrap.php
 * Remember to fill in the fields!
 */

Configure::write('UrbanAirship.key', '');
Configure::write('UrbanAirship.master', '');

Configure::write('Twilio.sid', '');
Configure::write('Twilio.token', '');
Configure::write('Twilio.number', '');

require APP . 'Plugin' . DS . 'NotificationManager' . DS . 'Lib' . DS . 'NotificationUtility.php';