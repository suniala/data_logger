<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Mittarien tilat</title>

<link href="plot.css" rel="stylesheet" type="text/css">
<?php 

include "../model.php";
include "../util.php";

$dao = new LoggerDao();

?>
</head>
<body>

	<h1>Mittareiden tilat</h1>

	<p>Tämä data päivittyy aina kun Arexxilta tulee palvelimelle asti
		mittauksia. Mittauksien suodatus ei siis vaikuta tähän dataan. Nimi
		näytetään sellaisille mittareille, joille on tietokannassa annettu
		nimi.</p>

	<table>
		<tr>
			<th>nimi</th>
			<th>tunniste</th>
			<th>tyyppi</th>
			<th>viimeisin mittaus</th>
			<th>viimeisin arvo</th>
		</tr>
		<?php
		$dev_hrtbts = $dao->find_device_heartbeats();
		foreach ($dev_hrtbts as $dev_hrtbt) {
			printf("<tr>");
			printf("<td>%s</td>", $dev_hrtbt->label);
			printf("<td>%s</td>", $dev_hrtbt->external_id);
			printf("<td>%s</td>", $dev_hrtbt->type_id);
			printf("<td>%s</td>", strftime("%d.%m.%Y klo %H.%M.%S", $dev_hrtbt->last_measurement_utc_s));
			printf("<td>%s</td>", $dev_hrtbt->last_value);
			printf("</tr>");
		}
		?>
	</table>

</body>
</html>
