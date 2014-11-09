create table device_heartbeat (
       id integer primary key,
       external_id text not null,
       type_id text not null,
       last_measurement_utc_s integer,
       last_value double not null
);

create unique index devhtbt_eid_idx on device_heartbeat(external_id, type_id);

insert into device_heartbeat
(external_id, type_id, last_measurement_utc_s, last_value)
select d.external_id, d.type_id, d.last_measurement_utc_s, 0.0
from device d
where d.last_measurement_utc_s is not null;