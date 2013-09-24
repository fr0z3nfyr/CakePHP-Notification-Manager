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