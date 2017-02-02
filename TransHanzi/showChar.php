<html><body>
<?php
$database=file_get_contents("UniVariants");
$records = explode("\n",$database);
for($i=20;$i<sizeof($records);$i++){
	$temp=explode("\t",$records[$i]);
	$decoded = array_merge(array($temp[0]),explode(" ",$temp[1]));
	for($j=0;$j<sizeof($decoded);$j++)
		print rehan($decoded[$j]);
	print "<br>";
}
function rehan($code){
    $res= mb_convert_encoding(pack("H*",str_repeat('0', 8 - strlen($code)).$code), 'UTF-8', 'UTF-32BE');
    return $res;
}
?>
</body></html>
