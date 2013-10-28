<?php

App::uses('NotificationUtility', 'NotificationManager.Lib');
App::uses('Notification', 'NotificationManager.Model');

/**
 * 
 */
class NotificationsShell extends AppShell
{
	public function main()
	{
        $NotificationModel = new Notification();
        
        $notifications = $NotificationModel->findAllBySentAndErrors(false, null);

        foreach ($notifications as $notification) {
            $response = NotificationUtility::notify($notification['Notification']);
            
            if ($response === true) {
                $NotificationModel->id = $notification['Notification']['id'];
                $NotificationModel->saveField('sent', true);
                $this->out($notification['Notification']['type'].' sent!');
            } else {
                $NotificationModel->id = $notification['Notification']['id'];
                $NotificationModel->saveField('errors', json_encode($response));
                $this->out($notification['Notification']['type'].' error!');
            }
        }
	}

}

