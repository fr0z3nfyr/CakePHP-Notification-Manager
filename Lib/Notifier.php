<?php

use UrbanAirship\Airship;
use UrbanAirship\UALog;
use UrbanAirship\Push as P;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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
            Notifier::push($notification);
        } else if ($notification->field('type') == 'EMAIL') {
            Notifier::email($notification);
        } else if ($notification->field('sms') == 'SMS') {
            Notifier::sms($notification);
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
                
        UALog::setLogHandlers(array(new StreamHandler("php://stdout", Logger::DEBUG)));

        $airship = new Airship(Configure::read('UrbanAirship.key'), Configure::read('UrbanAirship.secret'));

        try {
            $response = $airship->push()
                ->setAudience(P\tag($notification->field('user_id')))
                ->setNotification(P\notification($notification->field('subject')))
                ->setDeviceTypes(P\all)
                ->send();
            
            $notification->saveField('sent', true);
        } catch (AirshipException $e) {
            $notification->saveField('errors', json_encode($e));
        }
        
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
