<?php

include "model.php";
include "config.php";

function log_data($dao, $measurement)
{
	$dao->write_data($measurement);
	$dao->update_device_ts($measurement);
}

function route_data($dao, $measurement)
{
	if ($measurement->device->has_route()) {
		$get_url = $measurement->device->build_get_url($measurement);
		$handle = fopen($get_url, "r");
		stream_get_meta_data($handle);
	}
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

function parse_measurement($dao)
{
	$type = $_POST["type"];
	$id = $_POST["id"];
	$value = $_POST["value"];
	$timestamp = time();

	$m = null;
	$device = $dao->find_device_by_external_id($id, $type);

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

$dao = new LoggerDao();
try {
	$dao->beginTransaction();

	$measurement = parse_measurement($dao);
	if ($measurement != null) {
		if (!skip_logging($measurement)) {
			log_data($dao, $measurement);
			route_data($dao, $measurement);
		}
	}

	$dao->commit();
} catch (Exception $e) {
	$dao->rollBack();
	print($e);
}
?>
