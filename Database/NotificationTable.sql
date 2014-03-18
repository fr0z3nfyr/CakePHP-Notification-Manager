##
# Notifications table
##

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
  `send_on` datetime DEFAULT NULL,
  `timezone` varchar(128) DEFAULT 'UTC',
  `conditions` text,
  `sent` tinyint(1) DEFAULT '0',
  `sent_on` datetime DEFAULT NULL,
  `errors` text,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;