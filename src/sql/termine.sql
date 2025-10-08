delimiter ;

create table if not exists termine_kategorien (
    `id` varchar(36) primary key,
    `name` varchar(255) not null,
    `color` varchar(15) not null default '#ffffff',
    `icon` varchar(255) default null,
    `beschreibung` varchar(255) default null,
    `created` datetime not null default current_timestamp,
    `changed` datetime not null default current_timestamp on update current_timestamp
);

create table if not exists termine (
    `id` varchar(36) primary key,
    `begin` datetime not null,
    `end` datetime not null,
    `last_sync` datetime default null,
    `subject` varchar(255) default null,
    `body` text default null
);

create table if not exists termine_kategorie_zuordnung (
    `termin_id` varchar(36) not null,
    `kategorie_id` varchar(36) not null,
    primary key(`termin_id`,`kategorie_id`),
    foreign key(`termin_id`) references termine(`id`) on delete cascade,
    foreign key(`kategorie_id`) references termine_kategorien(`id`) on delete cascade
);