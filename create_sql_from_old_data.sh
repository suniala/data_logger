#!/bin/bash

input_dir=$1
device_db=$2

# 10 * 60 seconds
measurement_interval_s=600

output_dir=$(mktemp -d)
sql_file=$output_dir/init_old_data.sql

echo "Start time: "$(date)
echo "Output dir is $output_dir"

echo "Combining files..."
cat $input_dir/*.csv > $output_dir/all.csv

echo "Sorting combined data..."
cat $output_dir/all.csv | sort -k 1,1 -k 4,4 -k 2,2 -t ';' > $output_dir/all_sorted.csv

echo "Filtering and creating sql..."
curr_ext_id=''
curr_type_id=''
curr_cutoff_ts=''
curr_device_id=''

while read line; do
	fields=(${line//;/ })
	line_ext_id=${fields[0]}
	line_type_id=${fields[3]}
	line_ts=${fields[1]}
	line_value=${fields[4]}
	
	if [ "$curr_ext_id" != "$line_ext_id" ]; then
		curr_ext_id=$line_ext_id
		curr_cutoff_ts=''
		curr_device_id=''
		echo "New external id $curr_ext_id"
    fi
	if [ "$curr_type_id" != "$line_type_id" ]; then
		curr_type_id=$line_type_id
		curr_cutoff_ts=''
		curr_device_id=''
		echo "New type id $curr_type_id"
    fi
    
    if [ "$curr_device_id" = "" ]; then
    	curr_device_id=$(sqlite3 -batch $device_db "select id from device where external_id=$line_ext_id and type_id=$line_type_id;")

	    if [ "$curr_device_id" = "" ]; then
	    	echo "No device id found for external id $line_ext_id and type id $line_type_id. Bailing out."
	    	exit 1
	    fi
	    
	    echo "Current device id is $curr_device_id"
    fi
    
    # If this line's ts is after the current cutoff ts we should output the line.
    if [[ "$line_ts" > "$curr_cutoff_ts" ]]; then
    	# example: insert into measurement (device_id, taken_utc_s, value) values (25, 1393152940, 1.23);
		line_taken_utc_s=$(date -d "$line_ts" +%s)
    	echo "insert into measurement (device_id, taken_utc_s, value) values ($curr_device_id, $line_taken_utc_s, $line_value);" >> $output_dir/init_old_data.sql
    	
    	# Signal the need for a new cutoff ts.
    	curr_cutoff_ts=''
    fi
    
    # Create new cutoff ts if necessary.
    if [ "$curr_cutoff_ts" = "" ]; then
		# example: date -d "2013-01-08T03:04:05+02:00" +%Y-%m-%d"T"%H:%M:%S%z
		# Add the measurement interval
    	curr_cutoff_ts=$(date -d "$line_ts + $measurement_interval_s seconds" +%Y-%m-%d"T"%H:%M:%S%z)
    fi
done < $output_dir/all_sorted.csv

echo "Done"
echo "Output dir is $output_dir"
echo "End time: "$(date)
