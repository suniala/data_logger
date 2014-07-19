<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="examples.css" rel="stylesheet" type="text/css">
	<link href="jquery-ui.css" rel="stylesheet" type="text/css">
	<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="../../excanvas.min.js"></script><![endif]-->
	<script language="javascript" type="text/javascript" src="jquery.js"></script>
	<script language="javascript" type="text/javascript" src="jquery-ui.js"></script>
	<script language="javascript" type="text/javascript" src="jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="jquery.flot.time.js"></script>

	<script type="text/javascript">

	$(function() {

<?php 

include "../model.php";

$dev_id = $_GET["dev_id"];
$end_date_str = $_GET["end_date"];
$number_of_weeks = $_GET["weeks"];

if ($end_date_str == "") {
	$end_date_ts = time();
} else {
	$end_date_ts = strtotime($end_date_str);
}

if (filter_var($number_of_weeks, FILTER_VALIDATE_INT) == FALSE) {
	$number_of_weeks = 1;
}

$dao = new LoggerDao();
$measurements = $dao->find_measurements($dev_id, $end_date_ts, $number_of_weeks*7);
$current_device = $dao->find_device_by_id($dev_id);

print "var d = [";
foreach ($measurements as $measurement) {
	print "[" . $measurement->taken_utc_s*1000 . "," . $measurement->value . "],";
}
print "];";

?>
		
		$.plot("#placeholder", [d], {
			xaxis: { mode: "time" }
		});

	});

	</script>

	<script type="text/javascript">
	$(function() {
		$( "#datepicker" ).datepicker({ dateFormat: "yy-mm-dd", changeMonth: true,
			changeYear: true });
	});
	</script>

</head>
<body>

	<div id="content">

		<div>
			<form method="get">
				<ul>
					<li>Laite: 
						<select name="dev_id">
<?php 
$devices = $dao->find_devices();

foreach ($devices as $device) {
	$select_attr = "";
	if ($current_device->id == $device->id) {
		$select_attr = " selected";
	}
	
	print "<option "
		. "value=\"" . $device->id . "\""
		. $select_attr
		. ">"
		. $device->label
		. "</option>";
}
?>
						</select>
					</li>
					<li>Loppupäivä: 
						<input type="text" name="end_date" id="datepicker" value="<?php print date("Y-m-d", $end_date_ts)?>"/>
					</li>
					<li>Viikkoja:
						<select name="weeks">
<?php 
for ($weeks_option=1; $weeks_option<=14; $weeks_option++) {
	$weeks_select_attr = "";
	if ($number_of_weeks == $weeks_option) {
		$weeks_select_attr = " selected";
	}
	print "<option value=\"".$weeks_option."\"".$weeks_select_attr.">"
		.$weeks_option
		."</option>";
}
?>
						</select>
					</li>
					<li><input type="submit" value="avaa" /></li>
				</ul>
				
			</form>
		</div>

		<div>
<?php if ($current_device != null) { ?>
			<ul class="properties">
				<li>Laitteen nimi: <?php print $current_device->label ?></li>
				<li>Ulkoinen tunniste: <?php print $current_device->external_id ?></li>
				<li>Tyyppi: <?php print $current_device->type_id ?></li>
				<li>Sisäinen tunniste: <?php print $current_device->id ?></li>
			</ul>
<?php }?>
		</div>
		
		<div class="demo-container">
			<div id="placeholder" class="demo-placeholder"></div>
		</div>

	</div>

</body>
</html>
