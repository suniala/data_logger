<?php 

include "../config.php";
include "../util.php";

$value = http_get("value");
$utc_ts = http_get("utc_ts");
$password = http_get("key");

if ($ROUTING_PASSWORD == $password) {
	$log_handle = fopen("value.dat", "w");
	fwrite($log_handle, $value.";".$utc_ts);
	fclose($log_handle);
}

?>