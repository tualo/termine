delimiter ;


create table if not exists msgraph_calendars (
    `id`  varchar(36) not null,
    `login` varchar(128) not null,
    `name` varchar(128) not null,
    `msgraph_calendars_id` varchar(164) not null,
    `sync_termine` tinyint(1) not null default 0,
    `raw` json default '{}',
    primary key(`id`),
    unique key `uidx_msgraph_calendars_id` (`msgraph_calendars_id`),
    key `idx_msgraph_calendars_name` (`name`),
    key `idx_msgraph_calendars_login` (`login`)
) ;

create table if not exists msgraph_termine (
    `id`  varchar(36) not null,
    `termin_id` varchar(36) default null,
    `msgraph_calendars_id` varchar(164) not null,
    `msgraph_event_id` varchar(164) default null,
    `etag` varchar(148) default null,
    `iCalUId` varchar(255) default null,
    `update_uuid` varchar(36),
    primary key(`id`),


    key `idx_termin_id` (`termin_id`),
    key `idx_msgraph_event_id` (`msgraph_event_id`),
    key `idx_msgraph_calendars_id` (`msgraph_calendars_id`),

    constraint `fk_msgraph_termine_termin_id` foreign key(`termin_id`) references termine(`id`) on delete set null on update cascade,
    constraint `fk_msgraph_termine_msgraph_calendars_id` foreign key(`msgraph_calendars_id`) references msgraph_calendars(`msgraph_calendars_id`) on delete cascade on update cascade,
    `last_sync` datetime default null,
    `raw` longtext default '{}'
) ;


create or replace view view_sync_msgraph_termine as
    select 
        termine.*,
        msgraph_termine.id msgraph_termine_id,
        json_object(
            'transactionId', termine.id,
            'reminderMinutesBeforeStart', termine.remind_minutes_before,
            'subject', termine.subject,
            'body', json_object(
                'contentType', 'HTML',
                'content', if(termine.body is null,'',termine.body)
            ),
            'isAllDay', if(termine.is_all_day=1,1=1,0=1),
            'start', json_object(
                'dateTime', date_format(concat(termine.begin_date, ' ',  if(termine.is_all_day=1 , '00:00:00' , termine.begin_time ) ), '%Y-%m-%dT%H:%i:%s'),
                'timeZone', 'Europe/Berlin'
            ),
            'end', json_object(
                'dateTime', date_format(concat( if(termine.is_all_day=1 , termine.begin_date+interval 1 day , termine.end_date ), ' ',  if(termine.is_all_day=1 , '00:00:00' , termine.end_time ) ), '%Y-%m-%dT%H:%i:%s'),
                'timeZone', 'Europe/Berlin'
            )
        ) as msgraph_event,
        if (msgraph_termine.termin_id is null,'new',
            if(
                termine.update_uuid<>msgraph_termine.update_uuid,
                'push',
                'check'
            )
        ) __type,
        msgraph_calendars.login,
        msgraph_termine.msgraph_event_id,
        termine.id as termin_id,
        msgraph_calendars.msgraph_calendars_id,
        msgraph_calendars.sync_termine
from 
    termine 
    join 
        msgraph_calendars
        on  msgraph_calendars.sync_termine = 1
    left join
        msgraph_termine
        on termine.id = msgraph_termine.termin_id
            and msgraph_termine.msgraph_calendars_id = msgraph_calendars.msgraph_calendars_id   
where 
    begin_date >= curdate()
;

