#!/usr/bin/php
<?php

$CSVFILE=__DIR__."/Journals_WIAS.csv";

$handle = fopen($CSVFILE, "r");
if ($handle) {
    echo '<?xml version="1.0" encoding="UTF-8"?> '."\n";
    echo "<ZDBD2modsImport>\n";
    while (($data = fgetcsv($handle, 0, ";")) !== false) {
    	if ($data[1] != '' && $data[1] != '-') { 
        	echo '  <journal>'."\n";
        	echo '    <zdbdid>'.htmlspecialchars($data[1]).'</zdbdid>'."\n";
        	echo '  </journal>'."\n";
        };
        if ($data[2] != '' && $data[2] != '-') {
        	echo '  <journal>'."\n";
        	echo '    <zdbdid>'.$data[2].'</zdbdid>'."\n";
        	echo '  </journal>'."\n";
        }
    }
    echo "</ZDBD2modsImport>"."\n";
    fclose($handle);
} else {
    die ("Konnte Journals_WIAS.csv nicht öffnen.\n");
}


?>
