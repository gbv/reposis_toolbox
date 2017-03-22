#!/usr/bin/php
<?php

$CSVFILE=__DIR__."Journals_WIAS.csv";

$handle = fopen($CSVFILE, "r");
if ($handle) {
    echo '<?xml version="1.0" encoding="UTF-8"?> \n';
    echo "<ZDBD2modsImport>";
    while (($data = fgetcsv($handle, 0, ";")) !== false) {
        echo '  <journal>';
        echo '    <title>'.$data[0].'</title>';
        echo '    <issn type="print">'.$data[1].'</title>';
        echo '    <issn type="online">'.$data[2].'</title>';
        echo '  </journal>';
    }
    echo "</ZDBD2modsImport>";
    fclose($handle);
} else {
    die ("Konnte Journals_WIAS.csv nicht öffnen.");
}


?>