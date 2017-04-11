#!/usr/bin/php
<?php

/**********
 *
 *
 * see http://stackoverflow.com/questions/1835177/how-to-use-xmlreader-in-php
 ************************/
 
include ("lib/ZDBD.lib.php");

$logfilename = 'log/ZDBD2mods.log';
$LOG = fopen ($logfilename,'w');

function logging ($msg) {
    global $LOG;
    fwrite ($LOG,$msg);
}
 
$xml = new XMLReader();
$xml->open("php://stdin");
echo "read XML Data from std-Input\n";

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

$unprocessedJournals=array();

while($xml->next("journal")) {
    
    $xmlJornal =  $xml->readOuterXML();
    $journal = new SimpleXMLElement($xmlJornal);
    $title = (isset ($journal->title)) ? $journal->title : null;
    $issn_print = (isset ($journal->xpath("issn[@type='print']") [0])) ? $journal->xpath("issn[@type='print']") [0] : null;
    $issn_online = (isset ($journal->xpath("issn[@type='online']") [0])) ? $journal->xpath("issn[@type='online']") [0] : null;
    $zdbdid = (isset ($journal->zdbdid)) ? $journal->zdbdid : null;
    
    logging (  "Process Journal: ".$title."\n" );
    $success = false;
    if ($zdbdid) {
        logging ("  Try to get rdf by zdbdid (".$zdbdid.") \n");
        if ($success = downloadRDFbyZDBDID($zdbdid))  {
            continue;
        } 
    } 
    
    if ( $success===false && ($issn_print || $issn_online)) {
        logging ( "  Try to get rdf by issn (".$issn_print.",".$issn_online.") \n");
        if ($success = downloadRDFbyISSN($issn_print,$issn_online,$title))  {
            continue;
        } 
    } 
    
    if ( $success===false && ($title)) {
        logging ( "  Try to get rdf by title \n");
        if ($success = downloadRDFbyTitle($title))  {
            continue;
        }
    }
    
    logging ( "  Can't  get Journal from ZDB. \n");
    array_push($unprocessedJournals,$xmlJornal);
    
}

echo "Unprocessed Jounals:".count($unprocessedJournals)."\n";
echo "  see unprocessedJournals.xml \n";

$fh = fopen ('unprocessedJournals.xml','w');
fwrite ($fh, '<?xml version="1.0" encoding="UTF-8"?> '."\n");
fwrite ($fh, "<ZDBD2modsImport>\n");

foreach ($unprocessedJournals as $unprocessedJournal) {
    fwrite ($fh, $unprocessedJournal);
}
fwrite ($fh, "</ZDBD2modsImport>\n");
fclose ($fh);

?>