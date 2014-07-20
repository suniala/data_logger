<?php 

header("Content-Type: application/json");

include "../model.php";
include "../util.php";

$dev_id = http_get("dev_id");
$end_date_str = http_get("end_date");
$number_of_weeks = http_get("weeks");

if ($end_date_str == "") {
	$end_date_ts = time();
} else {
	$end_date_ts = strtotime($end_date_str);
}

if (filter_var($number_of_weeks, FILTER_VALIDATE_INT) == FALSE) {
	$number_of_weeks = 1;
}

$dao = new LoggerDao();
$measurements = $dao->find_measurements($dev_id, $end_date_ts, $number_of_weeks*7);
$current_device = $dao->find_device_by_id($dev_id);

print "{";
print "\"label\": \"".$current_device->label."\",";
print "\"data\": [";
$first = true;
foreach ($measurements as $measurement) {
	if (!$first) {
		print ",";
	} else {
		$first = false;
	}
	print "[" . $measurement->taken_utc_s*1000 . "," . $measurement->value . "]";
}
print "]";
print "}";
?>
