<?php

use UrbanAirship\Airship;
use UrbanAirship\UALog;
use UrbanAirship\Push as P;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * 
 */
class Notifier
{
    private static function getProperty($notification)
    {
        App::uses($notification['model'], 'Model');
        
        $model = $notification['model'];
        
        $obj = new $model($notification['object_id']);
        $obj->read();
        
        return $obj->field($notification['property']);
    }
    
	public static function notify($notification)
    {
        $data = json_decode($notification['data']);
        
        switch ($notification['type']) {
            case 'PUSH':
                return (object) [
                    'to' => P\tag(static::getProperty($notification)),
                    'notification' => P\notification($data->notification),
                    'devices' => P\all
                ];
            case 'EMAIL':
                return (object) [
                    'settings' => !empty($data->settings) ? $data->settings : 'default',
                    'vars' => !empty($data->vars) ? $data->vars : [],
                    'template' => !empty($data->template) ? $data->template : 'default',
                    'format' => !empty($data->format) ? $data->format : 'html',
                    'subject' => !empty($data->subject) ? $data->subject : '',
                    'to' => static::getProperty($notification)
                ];
            case 'SMS':
                return false;
        }
        
        switch ($notification->field('type')) {
            case 'PUSH':
                Notifier::push($data);
                break;
            case 'EMAIL':
                Notifier::email($data);
                break;
            case 'SMS':
                Notifier::sms($data);
                break;
        }
        
        return true;
    }
    
    public static function push($data)
    {
        UALog::setLogHandlers(array(new StreamHandler("php://stdout", Logger::DEBUG)));

        $airship = new Airship(
            Configure::read('NotificationManager.UrbanAirship.key'), 
            Configure::read('NotificationManager.UrbanAirship.secret')
        );
        
        try {
            $response = $airship->push()
                ->setAudience($data->to)
                ->setNotification($data->notification)
                ->setDeviceTypes($data->deviceTypes)
                ->send();
            
            $notification->saveField('sent', true);
            $this->out('Push notification sent.')
        } catch (AirshipException $e) {
            $notification->saveField('errors', json_encode($e));
        }
        
        return true;
    }
    
    public static function email($data)
    {
        try {
            $email = new CakeEmail($data->settings);
            $email -> viewVars($data->vars)
                -> template($data->template)
                -> emailFormat($data->format)
                -> subject($data->subject)
                -> to($data->to)
                -> send();
            
            $notification->saveField('sent', true);
            $this->out('Email notification sent.')
        } catch (Exception $e) {
            $notification->saveField('errors', json_encode($e->getMessage()));
        }
        
        return true;
    }
    
    public static function sms($data)
    {
        return true;
    }
}
