-- Foreign keys must be enabled explicitly: http://sqlite.org/foreignkeys.html
PRAGMA foreign_keys = ON;

create table measurement (
       id integer primary key,
       device_id integer not null,
       taken_local_ts integer not null,
       value double not null,
       foreign key(device_id) references device(id)
);

create table device (
       id integer primary key,
       external_id text not null,
       type_id text not null,
       label text not null,
       last_measurement_local_ts integer
);

create unique index device_mid_idx on device(external_id, type_id);
