<?php

use UrbanAirship\Airship;
use UrbanAirship\UALog;
use UrbanAirship\Push as P;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

App::import('Vendor', 'twilio/sdk/Services/Twilio');

/**
 * 
 */
class Notifier
{
    private static function getProperty($notification)
    {
        App::uses($notification['model'], 'Model');
        
        $model = $notification['model'];
        
        $obj = new $model();
        
        if (empty($notification['object_id_field'])) {
            $notification['object_id_field'] = 'id';
        }
        
        return Hash::extract($obj->find('all', [
            'conditions' => [
                $notification['object_id_field'] => $notification['object_id']
            ]
        ]), '{n}.'.$notification['model'].'.'.$notification['property']);
    }
    
	public static function notify($notification)
    {
        $data = json_decode($notification['data']);
        
        $notify = new stdClass();
        
        // Get the property to contact
        try {
            $property = static::getProperty($notification);
            
            if ($notification['type'] == 'PUSH') {
                if (is_array($property) && count($property) > 1) {
                    $property = P\and_($property);
                    
                    foreach ($property['and'] as &$prop) {
                        foreach ($prop as $key => $p) {
                            $prop['device_token'] = $p;
                            unset($prop[$key]);
                        }
                    }
                } else if (is_array($property)) {
                    $property = P\deviceToken($property[0]);
                } else {
                    $property = P\deviceToken($property);
                }                    
            }
        } catch (Exception $e) {
            if (!empty($data->to)) {
                $property = $data->to;
            } else {
                return false;
            }
        }
        // pr($property);exit;
        // If property is empty (find error)
        // Backup into the contact field in the data
        if (empty($property) && !empty($data->to)) {
            $property = $data->to;
        } else if (empty($property)) {
            return false;
        }
        
        switch ($notification['type']) {
            case 'PUSH':
                $notify->to = $property;
                if ((!empty($data->payload))) {
                    $notify->notification = P\notification(
                        $data->notification,
                        [
                            "ios" => P\ios(
                                $data->notification,
                                "+1",
                                "",
                                false,
                                (!empty($data->payload)) ? $data->payload : ''
                            )
                        ]
                    );
                } else {
                    $notify->notification = P\notification(
                        $data->notification,
                        [
                            "ios" => P\ios(
                                $data->notification,
                                "+1",
                                "",
                                false
                            )
                        ]
                    );
                }
                $notify->deviceTypes = P\all;
                break;
            case 'EMAIL':
                $notify->to = $property;
                $notify->settings = !empty($data->settings) ? $data->settings : 'default';
                $notify->vars = !empty($data->vars) ? $data->vars : [];
                $notify->template = !empty($data->template) ? $data->template : 'default';
                $notify->format = !empty($data->format) ? $data->format : 'html';
                $notify->subject = !empty($data->subject) ? $data->subject : '';
                break;
            case 'SMS':
                $notify->to = $property;
                $notify->notification = $data->notification;
                break;
        }

        switch ($notification['type']) {
            case 'PUSH':
                Notifier::push($notify);
                break;
            case 'EMAIL':
                Notifier::email($notify);
                break;
            case 'SMS':
                Notifier::sms($notify);
                break;
        }
        
        return true;
    }
    
    public static function push($data)
    {
        UALog::setLogHandlers(array(new StreamHandler("php://stdout", Logger::DEBUG)));

        $airship = new Airship(
            Configure::read('NotificationManager.UrbanAirship.key'), 
            Configure::read('NotificationManager.UrbanAirship.master')
        );
        
        try {
            $response = $airship->push()
                ->setAudience($data->to)
                ->setNotification($data->notification)
                ->setDeviceTypes($data->deviceTypes)
                ->send();
        } catch (AirshipException $e) {
            return $e->getMessage();
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
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
        return true;
    }
    
    public static function sms($data)
    {
        try {
            $client = new Services_Twilio(
                Configure::read('NotificationManager.Twilio.sid'),
                Configure::read('NotificationManager.Twilio.token')
            );
            $message = $client->account->sms_messages->create(
                Configure::read('NotificationManager.Twilio.number'),
                $data->to,
                $data->notification
            );
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
        return true;
    }
}