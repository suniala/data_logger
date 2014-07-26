<?php 

include "../config.php";
include "../util.php";

$value = http_get("value");

$log_handle = fopen("value.dat", "w");
fwrite($log_handle, $value."\n");
fclose($log_handle);

?>