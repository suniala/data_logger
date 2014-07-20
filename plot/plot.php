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

$dev_id = http_get("dev_id");
$end_date_str = http_get("end_date");
$number_of_weeks = http_get("weeks");
$scale = http_get("scale");

if ($end_date_str == "") {
	$end_date_ts = time();
} else {
	$end_date_ts = strtotime($end_date_str);
}

if (filter_var($number_of_weeks, FILTER_VALIDATE_INT) == FALSE) {
	$number_of_weeks = 1;
}

$dao = new LoggerDao();
$current_device = $dao->find_device_by_id($dev_id);

if ($scale == "fixed") {
	if ($current_device->type_id == 1) {
		$scale_args_y = "ticks: 10, min: -30, max: 30, tickDecimals: 0";
	} else {
		$scale_args_y = "ticks: 10, min: 0, max: 100, tickDecimals: 0";
	}
} else {
	$scale = "auto";
	$scale_args_y = "";
}

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
		var options = {
			lines: {
				show: true
			},
			points: {
				show: false
			},
			yaxis: {
				<?php print $scale_args_y ?>
			},
			xaxis: {
				mode: "time"
			}
		};

		var data = [];

		$.plot("#placeholder", data, options);

		var alreadyFetched = {};

		var dataurl = "<?php printf("series.php?dev_id=%s&end_date=%s&weeks=%s", $dev_id, $end_date_str, $number_of_weeks); ?>";

		function onDataReceived(series) {
			// Push the new data onto our existing data array
			if (!alreadyFetched[series.label]) {
				alreadyFetched[series.label] = true;
				data.push(series);
			}

			$.plot("#placeholder", data, options);
		}

		$.ajax({
			url: dataurl,
			type: "GET",
			dataType: "json",
			success: onDataReceived
		});
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
	if ($current_device != null && $current_device->id == $device->id) {
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
						<input name="weeks" id="weeks" value="<?php print $number_of_weeks ?>" />
					</li>
					<li>Asteikko:
						<input id="scale-auto" type="radio" name="scale" value="auto" <?php if ($scale=="auto") { print "checked"; } ?>/><label for="scale-auto">auto</label>
						<input id="scale-fixed" type="radio" name="scale" value="fixed" <?php if ($scale=="fixed") { print "checked"; } ?>/><label for="scale-fixed">kiinteä</label>
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

		<div class="plot-container">
			<div id="placeholder" class="plot-placeholder"></div>
		</div>

	</div>
</body>
</html>
