# CakePHP Notification Manager

MySQL and CRON based notification manager for CakePHP.

## Background

Supports notification setup for email, push notification, and sms.

## Requirements

* PHP >= 5.3
* CakePHP 2.x
* UrbanAirship account for push notifications
* Twillio account for sms notifications (coming soon)

## Installation

_[Manual]_

* Download this: http://github.com/asugai/CakePHP-Notification-Manager/zipball/master
* Unzip that download.
* Copy the resulting folder to app/Plugin
* Rename the folder you just copied to NotificationManager

_[GIT Submodule]_

In your app directory type:

	git submodule add git://github.com/asugai/CakePHP-Notification-Manager.git Plugin/NotificationManager
	git submodule update --init

_[GIT Clone]_

In your app directory type

	git clone git://github.com/asugai/CakePHP-Notification-Manager.git Plugin/NotificationManager

### Enable plugin

Enable the plugin your `app/Config/bootstrap.php` file:

	CakePlugin::load('NotificationManager');

If you are already using `CakePlugin::loadAll();`, then this is not necessary.

## Usage

### Setup NotificationManager

Setup the `notifications` table:

    CREATE TABLE `notifications` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `model` varchar(128) DEFAULT NULL,
      `object_id` int(11) DEFAULT NULL,
      `type` enum('EMAIL','PUSH','SMS') DEFAULT NULL,
      `subject` varchar(256) DEFAULT NULL,
      `vars` text,
      `template` varchar(256) DEFAULT NULL,
      `sent` tinyint(1) DEFAULT '0',
      `errors` text,
      `created` datetime DEFAULT NULL,
      `modified` datetime DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    

Edit the `/app/Plugin/NotificationManager/Config/bootstrap.php` file and update `UrbanAirship` keys:

    Configure::write('UrbanAirship.app.key', 'xxxxxxxxxxxxxxxxxxxx');
    Configure::write('UrbanAirship.app.secret', 'xxxxxxxxxxxxxxxx');
    Configure::write('UrbanAirship.app.masterSecret', 'xxxxxxxxxxxxxxxx');

Set up notification events in your models.

Example - Send `Welcome` email for a new user:

    App::uses('Notification', 'NotificationManager.Model');
    
    ...
    
    public function register()
    {
        ...
        
        $notification = [
            'model' => 'User', // name of the object model
            'object_id' => $this->id, // id of the object
            'type' => 'EMAIL', // Type of notification, can be EMAIL, PUSH, or SMS
            'subject' => 'Welcome to my new app!', // The subject line of the email
            'vars' => json_encode([ // In this case vars for the email template
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'test@test.com'
            ]),
            'template' => 'welcome' // Template name for email
        ];
    
        try {
            $NotificationModel = new Notification();
            $NotificationModel->create();
            $NotificationModel->save($notification);  
        } catch (Exception $e) {
            // failure catch
        }
        
        ...
    }
	

Example - Send push notification to a user:

    App::uses('Notification', 'NotificationManager.Model');
    
    ...
    
    public function pushIt()
    {
        ...
    
        $notification = [
            'model' => 'User',
            'object_id' => $this->id,
            'type' => 'PUSH',
            'subject' => 'You got a new push!', // This is the copy for the push
            'vars' => NULL, // Not used for PUSH type
            'template' => 'welcome' // Template name doesn't need to be used
        ];
        
        try {
            $NotificationModel = new Notification();
            $NotificationModel->create();
            $NotificationModel->save($notification);  
        } catch (Exception $e) {
            // failure catch
        }
    
        ...
    }

Set up a `cronjob` to run the notifications in the background:

    $ sudo crontab -e

Example `cronjob` that runs the `NotificationManager.Notifications` every minute:

    */1 * * * * /var/www/html/app/Console/cake NotificationManager.Notifications

## Todo

* Add device specific push notification
* Set up more error checking
* Add SMS
* Add Unit tests!

## License

Copyright (c) 2013 Andre Sugai

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.