<?php

App::uses('CakeEmail', 'Network/Email');
App::import('Vendor', 'twilio/sdk/Services/Twilio');

/**
 * 
 */
class NotificationUtility
{
    public static function notify($notification)
    {
        $data = json_decode($notification['data']);
        $notify = [];
        
        try {
            $property = static::getProperty($notification);
        } catch (Exception $e) {
            if (!empty($data->to)) {
                $property = $data->to;
            } else {
                return 'Could not get property for notification: ' . $e->getMessage();
            }
        }

        if (empty($property) && !empty($data->to)) {
            $property = $data->to;
        } else if (empty($property)) {
            return false;
        }
        
        if (!empty($notification['condition'])) {
            $test = static::checkConditions($notification);
            
            if ($test !== true) {
                return 'Conditions not met: ' . $test;
            }
        }
        
        switch ($notification['type']) {
            case 'EMAIL':
                $notify['to'] = explode(',',$property);
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
            case 'EMAIL':
                return NotificationUtility::email($notify);
                break;
            case 'SMS':
                return NotificationUtility::sms($notify);
                break;
        }
        
        return true;
    }
    
    private static function getProperty($notification)
    {
        App::uses($notification['model'], 'Model');
        $model = $notification['model'];
        $obj = new $model();
        
        if (empty($notification['object_id_field'])) {
            $notification['object_id_field'] = 'id';
        }
        
        $field = $model.'.'.$notification['object_id_field'];
        $params = [
            'conditions' => [$field => $notification['object_id']],
            'recursive' => -1
        ];
        
        $extract = $model.'.'.$notification['property'];
        $row = $obj->find('first', $params);

        return Hash::get($row, $extract);
    }
    
    private static function checkConditions($notification)
    {
        App::uses($notification['model'], 'Model');
        $model = $notification['model'];
        $obj = new $model();
        
        if (empty($notification['object_id_field'])) {
            $notification['object_id_field'] = 'id';
        }
        
        if (!method_exists($obj, $notification['condition'])) {
            return false;
        }
        
        return $obj->{$notification['condition']}($notification);
    }
    
    public static function email($data)
    {
        try {
            $email = new CakeEmail();
            $email->config(!empty($data['settings']) ? $data['settings'] : 'default');
            $email->config($data)
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