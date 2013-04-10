CREATE TABLE `rights` (
  `id` int(11) NOT NULL auto_increment,
  `short_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `verified` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_rights` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `right_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `right_id` (`right_id`),
  CONSTRAINT `user_rights_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_rights_ibfk_2` FOREIGN KEY (`right_id`) REFERENCES `rights` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `config_values` (
  `id` int(11) NOT NULL auto_increment,
  `module` VARCHAR(45) NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `value` VARCHAR(45) NOT NULL,
  PRIMARY KEY  (`id`),
  CONSTRAINT `config_values_unique` UNIQUE (`module`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_config_values` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `module` VARCHAR(45) NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `value` VARCHAR(45) NOT NULL,
  PRIMARY KEY  (`id`),
  CONSTRAINT `user_config_values_unique` UNIQUE (`user_id`, `module`, `name`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_config_values_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

