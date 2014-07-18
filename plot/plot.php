<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="examples.css" rel="stylesheet" type="text/css">
	<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="../../excanvas.min.js"></script><![endif]-->
	<script language="javascript" type="text/javascript" src="jquery.js"></script>
	<script language="javascript" type="text/javascript" src="jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="jquery.flot.time.js"></script>
	<script type="text/javascript">

	$(function() {

<?php 

include "../model.php";

$dev_id = $_GET["dev_id"];

$dao = new LoggerDao();
$measurements = $dao->find_measurements($dev_id);
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
	print "<option value=\"" . $device->id . "\">" . $device->label . "</option>";
}
?>
						</select>
					</li>
				</ul>
				
				<input type="submit" />
			</form>
		</div>

		<div>
			<ul class="properties">
				<li>Laitteen nimi: <?php print $current_device->label ?></li>
				<li>Ulkoinen tunniste: <?php print $current_device->external_id ?></li>
				<li>Tyyppi: <?php print $current_device->type_id ?></li>
				<li>Sisäinen tunniste: <?php print $current_device->id ?></li>
			</ul>
		</div>
		
		<div class="demo-container">
			<div id="placeholder" class="demo-placeholder"></div>
		</div>

	</div>

</body>
</html>
