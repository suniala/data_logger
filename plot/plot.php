<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	
	<link href="plot.css" rel="stylesheet" type="text/css">
	<link href="jquery-ui-1.11.0/jquery-ui.css" rel="stylesheet" type="text/css">
	
	<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="../../excanvas.min.js"></script><![endif]-->
	<script language="javascript" type="text/javascript" src="jquery-ui-1.11.0/external/jquery/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="jquery-ui-1.11.0/jquery-ui.js"></script>
	<script language="javascript" type="text/javascript" src="jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="jquery.flot.time.js"></script>

<?php 

include "../model.php";
include "../util.php";

$dev_id_param = http_get("dev_id");
$end_date_str = http_get("end_date");
$number_of_weeks = http_get("weeks");

if (is_array($dev_id_param)) {
	$dev_ids = $dev_id_param;
} else {
	$dev_ids = array($dev_id_param);
}

if ($end_date_str == "") {
	$end_date_ts = time();
} else {
	$end_date_ts = strtotime($end_date_str);
}

if (filter_var($number_of_weeks, FILTER_VALIDATE_INT) == FALSE) {
	$number_of_weeks = 1;
}

$dao = new LoggerDao();

?>

	<script type="text/javascript">
	$(function() {
		$( "#datepicker" ).datepicker({ dateFormat: "yy-mm-dd", changeMonth: true,
			changeYear: true });

		$( "#weeks" ).spinner({ min: 1, max: 53 });
	});
	</script>

	<script type="text/javascript">
	$(function() {
		function tempFormatter(v, axis) {
			return v.toFixed(axis.tickDecimals) + " &deg;C"
		}
		
		function rhFormatter(v, axis) {
			return v.toFixed(axis.tickDecimals) + " %"
		}
		
		var options = {
			lines: {
				show: true
			},
			points: {
				show: false
			},
			xaxis: {
				mode: "time"
			},
			yaxes: [
				{ position: "left", tickFormatter: tempFormatter },
				{ position: "right", tickFormatter: rhFormatter }
			]
		};

		var data = [];

		$.plot("#placeholder", data, options);

		var alreadyFetched = {};

		function onDataReceived(series) {
			// Push the new data onto our existing data array
			if (!alreadyFetched[series.label]) {
				alreadyFetched[series.label] = true;
				data.push(series);
			}

			$.plot("#placeholder", data, options);
		}

<?php
foreach ($dev_ids as $dev_id) {
	$data_url = sprintf("series.php?dev_id=%s&end_date=%s&weeks=%s", $dev_id, $end_date_str, $number_of_weeks);
	printf("$.ajax({url: \"%s\", type: \"GET\", dataType: \"json\", success: onDataReceived});", $data_url);
}
?>
	});
	</script>
		
</head>
<body>

	<div id="content">

		<div>
			<form method="get">
<?php
$devices = $dao->find_devices();
$dev_map = array();
foreach ($devices as $device) {
	if (0 <> substr_compare("test", $device->label, 0, 4)) {
		if (!isset($dev_map[$device->external_id])) {
			$dev_map[$device->external_id] = array();
		}
		if ($device->type_id == 1) {
			$dev_map[$device->external_id]["temp"] = $device->id;
		} elseif ($device->type_id == 3) {
			$dev_map[$device->external_id]["rh"] = $device->id;
		}
	}
}
?>
				<div>
					<table id="device-selection">
					<tr>
					<th>&nbsp;</th>
					<?php 
					foreach ($dev_map as $dev_ext_id=>$dev_id_map) {
						printf("<th>%s</th>", $dev_ext_id);
					}
					?>
					</tr>
					<tr>
					<td>Lämpö</td>
					<?php 
					foreach ($dev_map as $dev_ext_id=>$dev_id_map) {
						if (isset($dev_id_map["temp"])) {
							$device_checked_attr = in_array($dev_id_map["temp"], $dev_ids) ? "checked" : "";
							printf("<td><input type=\"checkbox\" name=\"dev_id[]\" value=\"%s\"/ %s></td>", 
									$dev_id_map["temp"], $device_checked_attr);
						} else {
							printf("<td>-</td>");
						}
					}
					?>
					</tr>
					<tr>
					<td>Kosteus</td>
					<?php 
					foreach ($dev_map as $dev_ext_id=>$dev_id_map) {
						if (isset($dev_id_map["rh"])) {
							$device_checked_attr = in_array($dev_id_map["rh"], $dev_ids) ? "checked" : "";
							printf("<td><input type=\"checkbox\" name=\"dev_id[]\" value=\"%s\" %s/></td>", 
									$dev_id_map["rh"], $device_checked_attr);
						} else {
							printf("<td>-</td>");
						}
					}
					?>
					</tr>
					</table>				
				</div>		
				<ul>
					<li>Loppupäivä: 
						<input type="text" name="end_date" id="datepicker" value="<?php print date("Y-m-d", $end_date_ts)?>"/>
					</li>
					<li>Viikkoja:
						<input name="weeks" id="weeks" value="<?php print $number_of_weeks ?>" />
					</li>
					<li><input type="submit" value="avaa" /></li>
				</ul>
			</form>
		</div>

		<div class="plot-container">
			<div id="placeholder" class="plot-placeholder"></div>
		</div>

	</div>
</body>
</html>
