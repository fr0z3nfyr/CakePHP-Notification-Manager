<?php

App::uses('CakeEmail', 'Network/Email');

use UrbanAirship\Airship;
use UrbanAirship\UALog;
use UrbanAirship\Push as P;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

App::import('Vendor', 'twilio/sdk/Services/Twilio');

/**
 * 
 */
class NotificationUtility
{
    private static function getProperty($notification)
    {
        App::uses($notification['model'], 'Model');
        
        $model = $notification['model'];
        
        $obj = new $model();
        
        if (empty($notification['object_id_field'])) {
            $notification['object_id_field'] = 'id';
        }
        
        $params = [
            'conditions' => [
                $model.'.'.$notification['object_id_field'] => $notification['object_id']
            ],
            'recursive' => -1
        ];
        
        return Hash::extract($obj->find('all', $params), '{n}.'.$notification['model'].'.'.$notification['property']);
    }
    
	public static function notify($notification)
    {
        $data = json_decode($notification['data']);
        
        $notify = [];
        
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
                return 'Could not get property for notification';
            }
        }

        // If property is empty (find error)
        // Backup into the contact field in the data
        if (empty($property) && !empty($data->to)) {
            $property = $data->to;
        } else if (empty($property)) {
            return false;
        }
        
        switch ($notification['type']) {
            case 'PUSH':
                $notify['to'] = $property;
                if ((!empty($data->payload))) {
                    $notify['notification'] = P\notification(
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
                    $notify['notification'] = P\notification(
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
                $notify['deviceTypes'] = P\all;
                break;
            case 'EMAIL':
                $notify['to'] = $property;
                $notify = array_merge($notify, json_decode(json_encode($data), true));
                if (empty($notify['emailFormat']) && !empty($notify['format'])) {
                    $notify['emailFormat'] = $notify['format'];
                }
                if (empty($notify['viewVars']) && !empty($notify['vars'])) {
                    $notify['viewVars'] = $notify['vars'];
                }
                break;
            case 'SMS':
                $notify['to'] = $property;
                $notify['notification'] = $data->notification;
                break;
        }

        switch ($notification['type']) {
            case 'PUSH':
                return NotificationUtility::push($notify);
                break;
            case 'EMAIL':
                return NotificationUtility::email($notify);
                break;
            case 'SMS':
                return NotificationUtility::sms($notify);
                break;
        }
        
        return true;
    }
    
    public static function push($data)
    {
        UALog::setLogHandlers(array(new StreamHandler("php://stdout", Logger::DEBUG)));

        $airship = new Airship(
            Configure::read('UrbanAirship.key'), 
            Configure::read('UrbanAirship.master')
        );
        
        try {
            $response = $airship->push()
                ->setAudience($data['to'])
                ->setNotification($data['notification'])
                ->setDeviceTypes($data['deviceTypes'])
                ->send();
        } catch (AirshipException $e) {
            return $e->getMessage();
        }
        
        return true;
    }
    
    public static function email($data)
    {
        try {
            $email = new CakeEmail();
            $email -> config(!empty($data['settings']) ? $data['settings'] : 'default');
            $email -> config($data)
                -> send();
        } catch (Exception $e) {
            return json_encode($email) . ' ' . $e->getMessage();
        }
        
        return true;
    }
    
    public static function sms($data)
    {
        try {
            $client = new Services_Twilio(
                Configure::read('Twilio.sid'),
                Configure::read('Twilio.token')
            );
            $message = $client->account->sms_messages->create(
                Configure::read('Twilio.number'),
                $data['to'],
                $data['notification']
            );
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
        return true;
    }
}