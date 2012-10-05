<?php
include_once("config.php");


$result = mysql_query("SELECT * FROM tnews WHERE visibile=1 order by id asc");

$return;
$i=0;
while($row = mysql_fetch_array($result)) {
	$return[$i]["id"] = $row["id"];
	$return[$i]["titolo_it"] = $row["titolo_it"];
	$return[$i]["immagine"] = $row["immagine"];
	$return[$i]["descrizione_it"] = $row["descrizione_it"];
	$i++;
}
  
 
mysql_close($con);

echo json_encode($return);
?>