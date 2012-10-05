<?php
include_once("config.php");


$result = mysql_query("SELECT * FROM tprodotti WHERE visibile=1 order by id asc");

$return;
$i=0;
while($row = mysql_fetch_array($result)) {
	$return[$i]["id"] = $row["id"];
	$return[$i]["nome"] = $row["nome_en"];
	$return[$i]["immagine"] = $row["immagine"];
	$return[$i]["descrizione"] = $row["descrizione_en"];
	$i++;
}
  
 
mysql_close($con);

echo json_encode($return);
?>