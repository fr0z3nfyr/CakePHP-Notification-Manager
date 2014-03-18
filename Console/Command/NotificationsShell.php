<?php

App::uses('NotificationUtility', 'NotificationManager.Lib');
App::uses('Notification', 'NotificationManager.Model');

/**
 * 
 */
class NotificationsShell extends AppShell
{
    public $uses = ['Notification'];
    
	public function main()
	{
        $tableFields = array_keys($this->Notification->getColumnTypes());
        $timezone = date_default_timezone_get();
        
        $date = new DateTime();
        
        if ($timezone != 'UTC' || $timezone != 'GMT') {
            $date->setTimezone(new DateTimeZone('UTC'));
        }
        
        $params = [
            'conditions' => [
                'AND' => [
                    'Notification.sent' => false,
                    'Notification.errors' => null
                ]
            ]
        ];
        
        if (in_array('send_on', $tableFields)) {
            $params['conditions']['AND']['OR'] = [
                'Notification.send_on' => null,
                "CONVERT_TZ(Notification.send_on, Notification.timezone, 'UTC') <" => $date->format('Y-m-d H:i:s')
            ];
        }
        
        $notifications = $this->Notification->find('all', $params);
        
        foreach ($notifications as $notification) {
            $response = NotificationUtility::notify($notification['Notification']);
            
            if ($response === true) {
                $this->Notification->id = $notification['Notification']['id'];
                $this->Notification->saveField('sent', true);
                
                if (in_array('sent_on', $tableFields)) {
                    $this->Notification->saveField('sent_on', date('Y-m-d H:i:s'));
                }
                
                $this->out($notification['Notification']['type'].' sent!');
            } else {
                $this->Notification->id = $notification['Notification']['id'];
                $this->Notification->saveField('errors', json_encode($response));
                $this->out($notification['Notification']['type'].' error! '.json_encode($response));
            }
        }
	}

}