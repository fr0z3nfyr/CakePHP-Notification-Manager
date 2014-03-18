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
        $tz = date_default_timezone_get();
        
        $date = new DateTime();
        
        if ($tz != 'UTC' || $tz != 'GMT') {
            $date->setTimezone('UTC');
        }
        
        $NotificationModel = new Notification();
        
        try {
            $params = [
                'conditions' => [
                    'AND' => [
                        'Notification.sent' => false,
                        'Notification.errors' => null,
                        'OR' => [
                            'Notification.send_on' => null,
                            "CONVERT_TZ(Notification.send_on, Notification.timezone, 'UTC') <" => $date->format('Y-m-d H:i:s');
                        ]
                    ]
                ]
            ];
            
            $notifications = $NotificationModel->find('all', $params);
        } else {
            $notifications = $NotificationModel->findAllBySentAndErrors(false, null);
        }

        foreach ($notifications as $notification) {
            $response = NotificationUtility::notify($notification['Notification']);
            
            if ($response === true) {
                $NotificationModel->id = $notification['Notification']['id'];
                $NotificationModel->saveField('sent', true);
                
                try {
                    $NotificationModel->saveField('sent_on', date('Y-m-d H:i:s'));
                } catch (Exception $e) { /* ... */}
                
                $this->out($notification['Notification']['type'].' sent!');
            } else {
                $NotificationModel->id = $notification['Notification']['id'];
                $NotificationModel->saveField('errors', json_encode($response));
                $this->out($notification['Notification']['type'].' error!');
            }
        }
	}

}

