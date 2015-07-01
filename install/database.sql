# Dump of table cp_object_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cp_object_items`;

CREATE TABLE `cp_object_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `object_type` varchar(50) NOT NULL DEFAULT '',
  `login_required` bit(1) NOT NULL DEFAULT b'1',
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table cp_objectmeta
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cp_objectmeta`;

CREATE TABLE `cp_objectmeta` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `meta_name` varchar(255) DEFAULT NULL,
  `meta_value` text,
  `meta_item` int(11) DEFAULT NULL,
  `meta_object` varchar(55) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table cp_settings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cp_settings`;

CREATE TABLE `cp_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(255) DEFAULT NULL,
  `setting_value` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table cp_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cp_users`;

CREATE TABLE `cp_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(55) NOT NULL DEFAULT '',
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `last_name` varchar(100) NOT NULL DEFAULT '',
  `user_name` varchar(100) NOT NULL DEFAULT '',
  `pass_word` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

