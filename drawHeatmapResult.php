<?php

$file = "result";
$f = fopen($file, "r");

echo "<markers>\n";

while ( $line = fgets($f, 1000) ) {
	$linia = explode(']', $line);
	
	$geop = substr($linia[0], 1);
	$loca = substr($linia[1], 3);
	$geo = explode(',', $geop);
	$loc = explode('","', $loca);

	$i = 0;
	$j = 0;
	$result = "";

	while($i < count($geo)) {
		echo "\t<marker location=\"".$loc[$j]."\" title=\"".trim($linia[2])."\" lat=\"".$geo[$i]."\" lng=\"".$geo[$i+1]."\" />\n";

		$i = $i+2;
		$j++;
	}

} 
echo "</markers>";

?>
