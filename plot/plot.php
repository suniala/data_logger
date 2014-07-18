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
	
		<div class="demo-container">
			<div id="placeholder" class="demo-placeholder"></div>
		</div>

	</div>

</body>
</html>
