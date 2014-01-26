# CakePHP Notification Manager

MySQL and CRON based notification manager for CakePHP.

## Background

Supports notification setup for email, push notification.

## Requirements

* PHP >= 5.3
* CakePHP 2.x
* Basic knowledge of CRON setup
* UrbanAirship PHP SDK v2: https://github.com/urbanairship/php-library2.git
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
      `object_id_field` varchar(128) DEFAULT 'id',
      `object_id` varchar(256) DEFAULT NULL,
      `property` varchar(128) DEFAULT NULL,
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
    

Setup the `autoloader` if you are using composer in `/app/Config/bootstrap.php`:

    // Load composer autoload.
    require APP . '/Vendor/autoload.php';

    // Remove and re-prepend CakePHP's autoloader as composer thinks it is the most important.
    // See https://github.com/composer/composer/commit/c80cb76b9b5082ecc3e5b53b1050f76bb27b127b
    spl_autoload_unregister(array('App', 'load'));
    spl_autoload_register(array('App', 'load'), true, true);
    
    // Load the bootstrap file to load Notification Model
    CakePlugin::loadAll([
        'NotificationManager' => [
            'bootstrap' => true
        ]
    ]);

Edit `/app/Config/bootstrap.php` file and add `UrbanAirship` and `Twilio` keys:

    if (!Configure::check('UrbanAirship')) {
        Configure::write('UrbanAirship.key', '');
        Configure::write('UrbanAirship.master', '');
    }

    if (!Configure::check('Twilio')) {
        Configure::write('Twilio.sid', '');
        Configure::write('Twilio.token', '');
        Configure::write('Twilio.number', '');
    }

Set up notification events in your models.

Example - Send `Welcome` email for a new user:

    public $hasMany = [
        ...
        'Notification' => [
            'foreignKey' => 'object_id',
            'conditions' => [
                'Notification.model' => 'User'
            ]
        ],
        ...
    ];
    ...
    public function register()
    {
        ...
        $notification = [
            'model' => 'User', // name of the object model
            'object_id' => $this->id, // id of the object
            'property' => 'email', // property of the object that will be used to notify (ex. email, phone, cell)
            'type' => 'EMAIL', // Type of notification, can be EMAIL, PUSH, or SMS
            'data' => json_encode([
                'settings' => 'default', // email settings
                'subject' => 'Welcome!', // email subject
                'template' => 'welcome', // email template
                'emailFormat' => 'html', // email format
                'viewVars' => [ // email vars
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@example.com'
                ]
            ])
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
            'property' => 'id', // using tags right now, User IDs being used for the tags
            'type' => 'PUSH',
            'data' => json_encode([
                'notification' => 'You got a new push!', // This is the copy for the push
            ]),
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

Example `cronjob` that runs the `NotificationManager.Notifications` every minute, replace `/var/www/html/app/Console/cake` with the cake console location in your own setup:

    * * * * * /var/www/html/app/Console/cake NotificationManager.Notifications

## Todo

* Add namespaces
* Comments!
* Add device specific push notification
* Set up more error checking
* Add SMS
* Add Unit tests!

## Aknowledgements

The basic layout of this of this README was taken from https://github.com/dkullmann/CakePHP-Elastic-Search-DataSource

## License

Copyright (c) 2013 Andre Sugai

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.