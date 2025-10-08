delimiter ;

insert into `ds_class` (`class_name`) values ('Termine') on duplicate key update `class_name`=values(`class_name`);


create table if not exists termine_calendars (
    `id` varchar(36) primary key,
    `name` varchar(255) not null,
    `color` varchar(25) not null default '#ffffff',
    `icon` varchar(255) default null,
    `description` varchar(255) default null,
    `created` datetime not null default current_timestamp,
    `changed` datetime not null default current_timestamp on update current_timestamp
);

create table if not exists termine_categories (
    `id` varchar(36) primary key,
    `name` varchar(255) not null,
    `color` varchar(15) not null default '#ffffff',
    `icon` varchar(255) default null,
    `description` varchar(255) default null,
    `created` datetime not null default current_timestamp,
    `changed` datetime not null default current_timestamp on update current_timestamp
);

create table if not exists termine (
    `id` varchar(36) primary key,
    `begin_date` date not null,
    `end_date` date not null,
    `begin_time` time default '10:00:00',
    `end_time` time default '12:00:00',
    `is_all_day` tinyint(1) not null default 0,
    `is_manualy` tinyint(1) not null default 0,
    `last_sync` datetime default null,
    `subject` varchar(255) default null,
    `body` text default null
);

create table if not exists termine_category_assignment (
    `termin_id` varchar(36) not null,
    `category_id` varchar(36) not null,
    primary key(`termin_id`,`category_id`),
    foreign key(`termin_id`) references termine(`id`) on delete cascade,
    foreign key(`category_id`) references termine_categories(`id`) on delete cascade
);

create table if not exists termine_calendar_assignment (
    `termin_id` varchar(36) not null,
    `calendar_id` varchar(36) not null,
    `active` tinyint(1) not null default 1,
    primary key(`termin_id`),
    foreign key(`termin_id`) references termine(`id`) on delete cascade,
    foreign key(`calendar_id`) references termine_calendars(`id`) on delete cascade
);

create or replace view view_readtable_termine_calendar_assignment as
    select
        termine_calendars.id as calendar_id,
        termine.id as termin_id,
        ifnull(tca.active, 0) as active
        
    from

        termine_calendars
        join termine

        left join termine_calendar_assignment tca on tca.termin_id=termine.id
            and tca.calendar_id=termine_calendars.id
;


create or replace view view_calendar_termine as
    select
        termine.id,
        termine.begin_date,
        if(termine.is_all_day=1 , termine.begin_date+interval 1 day , termine.end_date ) as end_date,
        if(termine.is_all_day=1 , '00:00:00' , termine.begin_time ) as begin_time,
        -- termine.begin_time,
        -- termine.end_time,
        if(termine.is_all_day=1 , '00:00:00' , termine.end_time ) as end_time,
        termine.is_all_day,
        termine.is_manualy,
        termine.last_sync,
        termine.subject,
        termine.body,
        termine_calendar_assignment.calendar_id as calendar_id
    from

        termine_calendar_assignment
        join termine
            on termine_calendar_assignment.termin_id=termine.id
;