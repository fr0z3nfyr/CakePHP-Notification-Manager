<?php

use NotificationManager\Notifier;

App::uses('Notifier', 'NotificationManager.Lib');
App::uses('Notification', 'NotificationManager.Model');

/**
 * 
 */
class NotificationsShell extends AppShell
{
	public function main()
	{
        $NotificationModel = new Notification();
        
        $notifications = $NotificationModel->findBySentAndErrors(false, null);
        
        foreach ($notifications as $notification) {
            Notifier::notify($notification);
        }
	}

}

