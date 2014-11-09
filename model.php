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
	public $route_url = null;
	
	public function has_route()
	{
		return $this->route_url != null;
	}
	
	public function build_get_url($measurement)
	{
		global $ROUTING_PASSWORD;
		$get_url = sprintf("%s?value=%s&utc_ts=%s&key=%s", 
				$this->route_url, 
				$measurement->value, 
				$measurement->taken_utc_s,
				$ROUTING_PASSWORD);
		return $get_url;
	}
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
		
		$stmt = $this->dbh->prepare("select id, external_id, type_id, label, filename, last_measurement_utc_s, route_url from device");
		if ($stmt->execute(array())) {
			while ($row = $stmt->fetch()) {
				$devices[] = $this->_map_device_row($row);
			}
		}
	
		return $devices;
	}
	
	public function find_device_by_external_id($external_id, $type_id)
	{
		$stmt = $this->dbh->prepare("select id from device where external_id=? and type_id=?");
		if ($stmt->execute(array($external_id, $type_id))) {
			while ($row = $stmt->fetch()) {
				$dev_id = $row["id"];
				return $this->find_device_by_id($dev_id);
			}
		}

		return null;
	}

	public function find_device_by_id($dev_id)
	{
		$stmt = $this->dbh->prepare("select id, external_id, type_id, label, filename, last_measurement_utc_s, route_url from device where id=?");
		if ($stmt->execute(array($dev_id))) {
			while ($row = $stmt->fetch()) {
				return $this->_map_device_row($row);
			}
		}

		return null;
	}

	public function find_measurements($dev_id, $ts_end, $number_of_days)
	{
		$measurements = array();
		if ($ts_end == null) {
			$ts_end = time();
		}
		$begin_of_day = strtotime("midnight", $ts_end);
		$ts_end_of_day   = strtotime("tomorrow", $begin_of_day) - 1;
		
		$ts_begin = $ts_end_of_day - (60 * 60 * 24 * $number_of_days);
		
		$measurement_count = $this->_count_measurements($dev_id, $ts_begin, $ts_end_of_day);
		$measurement_skip_count = $this->_calculate_skip_count($measurement_count);
				
		$stmt = $this->dbh->prepare(
				"select value, taken_utc_s " .
				"from measurement " .
				"where device_id=? and taken_utc_s >= ? and taken_utc_s <= ?" .
				"order by taken_utc_s asc;");
		if ($stmt->execute(array($dev_id, $ts_begin, $ts_end_of_day))) {
			$skipped = 0;
			while ($row = $stmt->fetch()) {
				if ($skipped >= $measurement_skip_count) {
					$measurement = new Measurement();
					$measurement->taken_utc_s = $row["taken_utc_s"];
					$measurement->value = $row["value"];
					$measurements[] = $measurement;
					
					$skipped = 0;
				} else {
					$skipped = $skipped + 1;
				}
			}
		}

		return $measurements;
	}

	private function _calculate_skip_count($measurement_count)
	{
		$MAX_MEASUREMENTS = 500;
		
		$skip_count = 0;
		if ($measurement_count > $MAX_MEASUREMENTS) {
			// The division operator ("/") returns a float value unless 
			// the two operands are integers (or strings that get 
			// converted to integers) and the numbers are evenly 
			// divisible, in which case an integer value will be returned. 
			$ratio = $measurement_count / $MAX_MEASUREMENTS;
			$skip_count = ceil($ratio) - 1;
		}
		
		return $skip_count;
	}
	
	private function _count_measurements($dev_id, $ts_begin, $ts_end)
	{
		$stmt = $this->dbh->prepare(
				"select count(id) as m_count " .
				"from measurement " .
				"where device_id=? and taken_utc_s >= ? and taken_utc_s <= ?");
		if ($stmt->execute(array($dev_id, $ts_begin, $ts_end))) {
			while ($row = $stmt->fetch()) {
				return $row["m_count"];
			}
		}
		
		return 0;
	}
	
	private function _map_device_row($row)
	{
		$device = new Device();
		$device->id = $row["id"];
		$device->external_id = $row["external_id"];
		$device->type_id = $row["type_id"];
		$device->label = $row["label"];
		$device->filename = $row["filename"];
		$device->last_measurement_utc_s = $row["last_measurement_utc_s"];
		$device->route_url = $row["route_url"];
		return $device;
	}
}

?>