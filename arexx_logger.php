<?php

include "model.php";
include "config.php";

function log_data($dbh, $measurement)
{
	$dbh->write_data($measurement);
	$dbh->update_device_ts($measurement);
}

function skip_logging($measurement)
{
	global $MEASUREMENT_INTERVAL_SECONDS;
	$skip = TRUE;

	$curr_time = time();
	$mod_time = $measurement->device->last_measurement_utc_s;
	$diff_time = $curr_time - $mod_time;
	if ($diff_time > ($MEASUREMENT_INTERVAL_SECONDS)) {
		$skip = FALSE;
	} else {
		$skip = TRUE;
	}

	return $skip;
}

function parse_measurement($dbh)
{
	$type = $_POST["type"];
	$id = $_POST["id"];
	$value = $_POST["value"];
	$timestamp = time();

	$m = null;
	$device = $dbh->find_device($id, $type);

	if ($device != null) {
		$m = new Measurement();
		$m->device = $device;
		$m->value = $value;
		$m->taken_utc_s = $timestamp;
	}
	
	return $m;	
}

$key = $_POST["key"];
if ($key != $LOGGER_PASSWORD)
{
	die();
}

$dbh = new LoggerDao();
try {
	$dbh->beginTransaction();
	
	$measurement = parse_measurement($dbh);
	if ($measurement != null) {
		if (!skip_logging($measurement)) {
			log_data($dbh, $measurement);
		}
	}

	$dbh->commit();
} catch (Exception $e) {
	$dbh->rollBack();
	print($e);
}
?>
