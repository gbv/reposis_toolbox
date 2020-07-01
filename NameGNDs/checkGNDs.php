#!/usr/bin/php
<?php

$ObjectsWithGNDs = json_decode(file_get_contents('ObjectsWithGNDs.json'),true);

$NameGNDs= array();
echo "Read file NameGNDs.lst";
$handle = fopen("NameGNDs.lst", "r");
if ($handle) {
    while (($gnd = fgets($handle)) !== false) {
        $gnd=trim($gnd);
        $NameGNDs[$gnd]='y';
        
    }
    if (!feof($handle)) {
        echo "Fehler: unerwarteter fgets() Fehlschlag\n";
    }
    fclose($handle);
} else {
    die ("Error: Need file NameGNDs.lst. Please Execute getObjectsWithGNDs.sh");
}
echo "..done\n";

foreach ($ObjectsWithGNDs['response']['docs'] as $object) {
    $mycoreid = $object['id']; 
    foreach ( $object['mods.nameIdentifier'] as $gnd) {
	if (substr($gnd,0,4) == 'gnd:') {    
            $gnd=substr($gnd,4);	
            if (! preg_match('/^[0-9-]+X?$/',$gnd) ) {
                //if (substr($gnd,0,8) == '(DE-601)') continue;
                if (substr($gnd,0,8) == '(DE-588)') continue;
                echo "Error Malformed GND:".$mycoreid."-".$gnd."\n";
               
            }
	} else {
            continue;
        }
        if (isset($NameGNDs[$gnd])) {
            echo $mycoreid."-".$gnd."\n";
        }
    }
}
?>
