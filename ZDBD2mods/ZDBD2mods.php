#!/usr/bin/php
<?php

/**********
 *
 *
 * see http://stackoverflow.com/questions/1835177/how-to-use-xmlreader-in-php
 ************************/
 
include ("lib/ZDBD.lib.php");
 
$xml = new XMLReader();
$xml->open("php://stdin");

$xml->read();  // read root tag
if ($xml->name != "ZDBD2modsImport") {
    die ("Root tag <ZDBD2modsImport> is missing \n");
}

$xml->read();  
if ($xml->name == "#text") {
    $xml->read(); 
}
if ($xml->name != "journal") {
    die ("<journal> is missing \n");
}

while($xml->next("journal")) {
    echo $xml->name."\n";
    
    $journal = new SimpleXMLElement($xml->readOuterXML());
    $title = (isset ($journal->title) ? $journal->title : null;
    $issn_print = (isset ($journal->xpath("issn[@type='print']") [0]) ? $journal->xpath("issn[@type='print']") [0] : null;
    $issn_online = (isset ($journal->xpath("issn[@type='online']") [0]) ? $journal->xpath("issn[@type='online']") [0] : null;
    $zdbdid = (isset ($journal->zdbdid) ? $journal->zdbdid : null;
    
    echo "Process Journal".$title."\n";
    $success = false;
    if ($zdbdid) {
        echo "Try to get rdf by zdbdid (".$zdbdid ") ";
        ($success = downloadRDFbyZDBDID($zdbdid)) ? echo "success\n" : echo "fail\n";
    } 
    
    if ( $success===false && ($issn_print || $issn_online)) {
        echo "Try to get rdf by issn (".$issn_print.",".issn_print.") ";
        echo "not implemented \n";
    } 
    
    if ( $success===false && ($title)) {
        echo "Try to get rdf by title (".$title.") ";
        echo "not implemented \n";
    }
}

?>