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
	public $filename;
	public $last_measurement_utc_s;
}

class LoggerDao
{
	private $dbh;

	function __construct()
	{
		global $DATABASE_URL;
		$this->dbh = new PDO($DATABASE_URL);
		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function beginTransaction()
	{
		$this->dbh->beginTransaction();
	}

	public function commit()
	{
		$this->dbh->commit();
	}

	public function rollBack()
	{
		$this->dbh->rollBack();
	}

	public function write_data($measurement)
	{
		$stmt = $this->dbh->prepare("insert into measurement (device_id, taken_utc_s, value) values (?, ?, ?)");
		$stmt->bindParam(1, $measurement->device->id);
		$stmt->bindParam(2, $measurement->taken_utc_s);
		$stmt->bindParam(3, $measurement->value);
		$stmt->execute();
	}

	public function update_device_ts($measurement)
	{
		$stmt = $this->dbh->prepare("update device set last_measurement_utc_s = ? where id = ?");
		$stmt->bindParam(1, $measurement->taken_utc_s);
		$stmt->bindParam(2, $measurement->device->id);
		$stmt->execute();
	}

	public function find_devices()
	{
		$devices = array();
		
		$stmt = $this->dbh->prepare("select id, external_id, type_id, label, filename, last_measurement_utc_s from device");
		if ($stmt->execute(array())) {
			while ($row = $stmt->fetch()) {
				$device = new Device();
				$device->id = $row["id"];
				$device->external_id = $row["external_id"];
				$device->type_id = $row["type_id"];
				$device->label = $row["label"];
				$device->filename = $row["filename"];
				$device->last_measurement_utc_s = $row["last_measurement_utc_s"];
				$devices[] = $device;
			}
		}
	
		return $devices;
	}
	
	public function find_device($external_id, $type_id)
	{
		$stmt = $this->dbh->prepare("select id, external_id, type_id, label, filename, last_measurement_utc_s from device where external_id=? and type_id=?");
		if ($stmt->execute(array($external_id, $type_id))) {
			while ($row = $stmt->fetch()) {
				$device = new Device();
				$device->id = $row["id"];
				$device->external_id = $row["external_id"];
				$device->type_id = $row["type_id"];
				$device->label = $row["label"];
				$device->filename = $row["filename"];
				$device->last_measurement_utc_s = $row["last_measurement_utc_s"];
				return $device;
			}
		}

		return $null;
	}

	public function find_measurements($dev_id)
	{
		$measurements = array();
		$ts_end = time();
		$ts_begin = $ts_end - (60 * 60 * 24 * 7);
		
		$stmt = $this->dbh->prepare(
				"select value, taken_utc_s " .
				"from measurement " .
				"where device_id=? and taken_utc_s >= ? and taken_utc_s <= ?" .
				"order by taken_utc_s asc;");
		if ($stmt->execute(array($dev_id, $ts_begin, $ts_end))) {
			while ($row = $stmt->fetch()) {
				$measurement = new Measurement();
				$measurement->taken_utc_s = $row["taken_utc_s"];
				$measurement->value = $row["value"];
				$measurements[] = $measurement;
			}
		}

		return $measurements;
	}
}

?>