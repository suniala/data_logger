<html>
<head>
<meta charset="UTF-8">
<title>eko</title>
<link href="eko.css" rel="stylesheet" type="text/css">
</head>
<body>
	<div>
		<?php 

		$log_handle = fopen("value.dat", "r");
		$last_value = fread($log_handle, filesize("value.dat"));
		fclose($log_handle);

		$green_class = "green";
		$yellow_class = "yellow";
		$red_class = "red";
		if ($last_value > 60) {
			$green_class = $green_class." active";
		} elseif ($last_value > 40) {
			$yellow_class = $yellow_class." active";
		} else {
			$red_class = $red_class." active";
		}
		?>

		<div id="content">
			<div id="lights">
				<table>
					<tr>
						<td class="<?php print $red_class ?>">&nbsp;</td>
					</tr>
					<tr>
						<td class="<?php print $yellow_class ?>">&nbsp;</td>
					</tr>
					<tr>
						<td class="<?php print $green_class ?>">&nbsp;</td>
					</tr>
				</table>
			</div>

			<div id="info">
				<p>Veden lämpötila varaajassa on</p>
				<p id="temp">
					<?php print $last_value ?> &deg;C
				</p>
			</div>
		</div>
	</div>
</body>
</html>
