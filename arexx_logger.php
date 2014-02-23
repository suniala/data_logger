<?php

include "config.php";

class Measurement
{
	public $id;
	public $device;
	public $taken_utc_s;
	public $value;
}

class Device
{
	public $id;
	public $external_id;
	public $type_id;
	public $label;
	public $last_measurement_utc_s;
}

function log_data($dbh, $measurement)
{
	write_data($dbh, $measurement);
	update_device_ts($dbh, $measurement);
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

function write_data($dbh, $measurement)
{
	$stmt = $dbh->prepare("insert into measurement (device_id, taken_utc_s, value) values (?, ?, ?)");
	$stmt->bindParam(1, $measurement->device->id);
	$stmt->bindParam(2, $measurement->taken_utc_s);
	$stmt->bindParam(3, $measurement->value);
	$stmt->execute();
}

function update_device_ts($dbh, $measurement)
{
	$stmt = $dbh->prepare("update device set last_measurement_utc_s = ? where id = ?");
	$stmt->bindParam(1, $measurement->taken_utc_s);
	$stmt->bindParam(2, $measurement->device->id);
	$stmt->execute();
}

function find_device($dbh, $external_id, $type_id)
{
	$stmt = $dbh->prepare("select id, external_id, type_id, label, last_measurement_utc_s from device where external_id=? and type_id=?");
	if ($stmt->execute(array($external_id, $type_id))) {
		while ($row = $stmt->fetch()) {
			$device = new Device();
			$device->id = $row["id"];
			$device->external_id = $row["external_id"];
			$device->type_id = $row["type_id"];
			$device->label = $row["label"];
			$device->last_measurement_utc_s = $row["last_measurement_utc_s"];
			return $device;
		}
	}
	
	return null;
}

function parse_measurement($dbh)
{
	$type = $_POST["type"];
	$id = $_POST["id"];
	$value = $_POST["value"];
	$timestamp = time();

	$m = null;
	$device = find_device($dbh, $id, $type);

	if ($device != null) {
		$m = new Measurement();
		$m->device = $device;
		$m->value = $value;
		$m->taken_utc_s = $timestamp;
	}
	
	return $m;	
}

function get_connection()
{
	global $DATABASE_URL;
	$dbh = new PDO($DATABASE_URL);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

$key = $_POST["key"];
if ($key != $LOGGER_PASSWORD)
{
	die();
}

$dbh = get_connection();
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
