#!/usr/bin/php
<?php

$CSVFILE=__DIR__."/Journals_WIAS.csv";

$handle = fopen($CSVFILE, "r");
if ($handle) {
    echo '<?xml version="1.0" encoding="UTF-8"?> '."\n";
    echo "<ZDBD2modsImport>\n";
    while (($data = fgetcsv($handle, 0, ";")) !== false) {
        echo '  <journal>'."\n";
        echo '    <title>'.$data[0].'</title>'."\n";
        echo '    <issn type="print">'.$data[1].'</title>'."\n";
        echo '    <issn type="online">'.$data[2].'</title>'."\n";
        echo '  </journal>'."\n";
    }
    echo "</ZDBD2modsImport>"."\n";
    fclose($handle);
} else {
    die ("Konnte Journals_WIAS.csv nicht öffnen.\n");
}


?>
