<?php 

include "../config.php";
include "../util.php";

$value = http_get("value");

$log_handle = fopen("log.txt", "w");
fwrite($log_handle, $value."\n");
fclose($log_handle);

?>