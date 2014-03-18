ALTER TABLE `notifications` ADD `send_on` datetime DEFAULT NULL AFTER `data`, 
    ADD `timezone` varchar(128) DEFAULT 'UTC' AFTER `send_on`, 
    ADD `condition` varchar(128) DEFAULT NULL `timezone`,
    ADD `sent_on` datetime DEFAULT NULL AFTER `sent`;