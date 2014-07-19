<?php

function http_get($name)
{
	if (isset($_GET[$name])) {
		return $_GET[$name];
	} else {
		return null;
	}
}

?>