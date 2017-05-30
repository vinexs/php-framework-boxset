## ============================
##	 CMS Module
##	 Version 1.0
##	 Last Update: 2015-08-22
## ============================

CREATE TABLE IF NOT EXISTS `cms_user` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`login_id` varchar(100) NOT NULL,
	`password` varchar(100) NOT NULL,
	`name` varchar(100) NOT NULL,
	`email` varchar(255) NOT NULL,
	`gender` enum('','M','F') NOT NULL DEFAULT '',
	`birth` date NOT NULL,
	`avatar` varchar(255) NOT NULL,
	`role` enum('user','vip','admin') NOT NULL DEFAULT 'user',
	`last_login` datetime NOT NULL,
	`is_show` tinyint(1) NOT NULL DEFAULT '1',
	`is_delete` tinyint(1) NOT NULL DEFAULT '0',
	`create_by` int(11) NOT NULL DEFAULT '0',
	`create_at` datetime NOT NULL,
	`last_modified_by` int(11) NOT NULL DEFAULT '0',
	`last_modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`), KEY `is_show` (`is_show`), KEY `is_delete` (`is_delete`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `cms_user` (`id`, `login_id`, `password`, `name`, `email`, `gender`, `birth`, `avatar`, `role`, `last_login`, `is_show`, `is_delete`, `create_by`, `create_at`, `last_modified_by`, `last_modified_at`) VALUES
(1, 'admin', '*4ACFE3202A5FF5CF467898FC58AAB1D615029441', 'Administrator', 'admin@example.com', '', '1970-01-01', '', 'admin', '1970-01-01 00:00:00', 1, 0, 0, '1970-01-01 00:00:00', 0, '1970-01-01 00:00:00');

CREATE TABLE IF NOT EXISTS `cms_info` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `post_time` datetime NOT NULL,
  `type` enum('','company','personal') NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `is_show` tinyint(1) NOT NULL DEFAULT '1',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `create_by` int(11) NOT NULL,
  `create_at` datetime NOT NULL,
  `last_modified_by` int(11) NOT NULL,
  `last_modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `is_show` (`is_show`), KEY `is_delete` (`is_delete`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cms_photo` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `caption` varchar(255) NOT NULL,
  `is_show` tinyint(1) NOT NULL DEFAULT '1',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `create_by` int(11) NOT NULL,
  `create_at` datetime NOT NULL,
  `last_modified_by` int(11) NOT NULL,
  `last_modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `is_show` (`is_show`), KEY `is_delete` (`is_delete`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `default` (
  `id` int(11) NOT NULL,
  `is_show` tinyint(1) NOT NULL DEFAULT '1',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `create_by` int(11) NOT NULL,
  `create_at` datetime NOT NULL,
  `last_modified_by` int(11) NOT NULL,
  `last_modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `is_show` (`is_show`), KEY `is_delete` (`is_delete`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
