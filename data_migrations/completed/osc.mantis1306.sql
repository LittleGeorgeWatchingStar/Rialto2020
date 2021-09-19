DROP TABLE IF EXISTS `products_compatible_products`;

CREATE TABLE `products_compatible_products` (
    `products_id` int(11) not null,
    `connects_to` int(11) not null,
    primary key (`products_id`, `connects_to`),
    constraint products_compatible_products_fk_products_id
    FOREIGN KEY (`products_id`)
    REFERENCES `products` (`products_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
    constraint products_compatible_products_fk_connects_to
    FOREIGN KEY (`connects_to`)
    REFERENCES `products` (`products_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `products_features`;

create table products_features (
    `products_id` int(11) not null,
    `feature_name` varchar(50) NOT NULL DEFAULT '',
    `description` varchar (255) not null default '',
    `value` varchar(100) not null default '',
    `units` varchar(10) not null default '',
    `image` varchar(64) not null default '',
    primary key (`products_id`, `feature_name`),
    constraint products_features_fk_products_id
    FOREIGN KEY (`products_id`)
    REFERENCES `products` (`products_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) engine=InnoDB default charset=utf8;

DROP TABLE IF EXISTS `products_key_components`;

create table products_key_components (
    `products_id` int(11) not null,
    `name` varchar(50) NOT NULL DEFAULT '',
    `description` mediumtext NOT NULL,
    `specs` varchar(255) DEFAULT NULL,
    primary key (`products_id`, `name`),
    constraint products_key_components_fk_products_id
    FOREIGN KEY (`products_id`)
    REFERENCES `products` (`products_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) engine=InnoDB default charset=utf8;

DROP TABLE IF EXISTS `products_included`;

create table products_included (
    `products_id` int not null,
    `name` varchar(50) NOT NULL DEFAULT '',
    `description` text NOT NULL,
    `quantity` int NOT NULL DEFAULT '1',
    primary key (`products_id`, `name`),
    constraint products_included_fk_products_id
    FOREIGN KEY (`products_id`)
    REFERENCES `products` (`products_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) engine=InnoDB default charset=utf8;