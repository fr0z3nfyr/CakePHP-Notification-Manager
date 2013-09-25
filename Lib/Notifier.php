<?php

namespace OraInteractive\NotificationManager

App::uses('Notification', 'NotificationManager.Model');

/**
 * 
 */
class Notifier
{
	public static function notify($notification)
    {
        $notification = new Notification($notification['id']);
        $notification->read();
        
        if ($notification->field('type') == 'PUSH') {
            \OraInteractive\NotificationManager\Notifier::push($notification);
        } else if ($notification->field('type') == 'EMAIL') {
            \OraInteractive\NotificationManager\Notifier::email($notification);
        } else if ($notification->field('sms') == 'SMS') {
            \OraInteractive\NotificationManager\Notifier::sms($notification);
        }
        
        return true;
    }
    
    public static function push(\Notification $notification)
    {
        $content = json_encode([
            'audience' => [
                'tag' => $notification->field('user_id')
            ],
            'device_types' => 'all', 
            'notification' => [
                'alert' => $notification->field('subject')
            ]       
        ]);
        
        $session = curl_init(Configure::read('UrbanAirship.push.url')); 
        
        curl_setopt($session, CURLOPT_USERPWD, 
            Configure::read('UrbanAirship.app.key') . ':' . Configure::read('UrbanAirship.app.secret')
        ); 
        curl_setopt($session, CURLOPT_POST, True); 
        curl_setopt($session, CURLOPT_POSTFIELDS, $content); 
        curl_setopt($session, CURLOPT_HEADER, False); 
        curl_setopt($session, CURLOPT_RETURNTRANSFER, True); 
        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/vnd.urbanairship+json; version=3;'
        ]);
        
        $content = curl_exec($session); 
        
        // echo $content;
        
        $response = curl_getinfo($session);
        
        if ($response['http_code'] != 200) { 
            $notification->saveField('errors', json_encode($response));
        } else {
            $notification->saveField('sent', true);
        } 
        
        curl_close($session);
        
        return true;
    }
    
    public static function email(\Notification $notification)
    {
        $vars = json_decode($notification->field('vars'), true);
        
        try {
            $email = new CakeEmail('default');
            $email -> viewVars($vars)
                -> template($notification->field('template'), 'default')
                -> emailFormat('html')
                -> subject($notification->field('subject'))
                -> to(Notifier::getProperty($notification))
                -> send();
            
            $notification->saveField('sent', true);
        } catch (Exception $e) {
            $notification->saveField('errors', json_encode($e->getMessage()));
        }
        
        return true;
    }
    
    public static function sms(\Notification $notification)
    {
        return true;
    }
    
    private static function getProperty(\Notification $notification)
    {
        App::uses($notification->field('model'), 'Model');
        
        $model = $notification->field('model');
        
        $obj = new $model($notification->field('object_id'));
        $obj->read();
        
        return $obj->field($notification->field('property'));
    }
}
