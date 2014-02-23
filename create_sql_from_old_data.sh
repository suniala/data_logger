#!/bin/bash

input_dir=$1
output_dir=$(mktemp -d)
#output_dir=/tmp/tmp.xXZ2iHhR7A
sql_file=$output_dir/init_old_data.sql
#echo '' > $sql_file

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

while read line; do
	fields=(${line//;/ })
	line_ext_id=${fields[0]}
	line_type_id=${fields[3]}
	line_ts=${fields[1]}
	
	if [ "$curr_ext_id" != "$line_ext_id" ]; then
		curr_ext_id=$line_ext_id
		curr_cutoff_ts=''
		echo "New external id $curr_ext_id"
    fi
	if [ "$curr_type_id" != "$line_type_id" ]; then
		curr_type_id=$line_type_id
		curr_cutoff_ts=''
		echo "New type id $curr_type_id"
    fi
    
    # If this line's ts is after the current cutoff ts we should output the line.
    if [[ "$line_ts" > "$curr_cutoff_ts" ]]; then
    	echo $line_ts >> $output_dir/init_old_data.sql
    	
    	# Signal the need for a new cutoff ts.
    	curr_cutoff_ts=''
    fi
    
    # Create new cutoff ts if necessary.
    if [ "$curr_cutoff_ts" = "" ]; then
		# example: date -d "2013-01-08T03:04:05+02:00" +%Y-%m-%d"T"%H:%M:%S%z
		# Add 1 hour = 3600 seconds
    	curr_cutoff_ts=$(date -d "$line_ts + 3600 seconds" +%Y-%m-%d"T"%H:%M:%S%z)
    fi
done < $output_dir/all_sorted.csv

echo "Done"
echo "Output dir is $output_dir"
echo "End time: "$(date)
