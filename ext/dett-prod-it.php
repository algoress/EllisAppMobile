<?php
include_once("config.php");
$idprod = isset($_REQUEST["id_prod"]) ? (int) $_REQUEST["id_prod"] : 0;

$result = mysql_query("SELECT * FROM tprodotti WHERE id=".$idprod);

$return;
$i=0;
while($row = mysql_fetch_array($result)) {
	$return[$i]["nome"] = $row["nome_it"];
	$return[$i]["immagine"] = $row["immagine"];
	$return[$i]["descrizione"] = $row["descrizione_it"];
	$i++;
}
  
 
mysql_close($con);

echo json_encode($return);
?>