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
  `data` text,
  `sent` tinyint(1) DEFAULT '0',
  `errors` text,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;