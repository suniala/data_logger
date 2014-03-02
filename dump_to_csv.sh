#!/bin/bash

database=/var/www/data_logger/data/data_logger.db
output_dir=$(mktemp -d)

devices_id_filename=$(sqlite3 -batch $database "select id, filename from device;")

for device_id_filename in $devices_id_filename; do
    device_id=$(echo $device_id_filename | gawk -F'|' '{print $1}')
    device_filename=$(echo $device_id_filename | gawk -F'|' '{print $2}')
    echo "Device $device_id goes to file $device_filename"

    sqlite3 -batch -csv $database "select datetime(m.taken_utc_s, 'unixepoch', 'localtime'), m.value from measurement m where m.device_id=$device_id order by m.taken_utc_s;" > $output_dir/$device_filename
done

echo "Wrote files to $output_dir"
