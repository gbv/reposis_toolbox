<?php

/**
 * urn2derivate - Verschiebt eine URN aus den Mods-Daten in das Derivate
 * 
 *   Autor: Paul.Borchert@gbv.de
 *
 * Mit Hilfe eines Solrquery werden alle Dokumente ermittelt deren URN übertragen werden soll. 
 * Derzeit werden die Daten aus einem Verzeichniss gelesen, in dem die Daten vorher exportiert werden mussten.
 * 
 */

$MIRBASEURL="https://ub-deposit.fernuni-hagen.de/";

$SEARCHBASEURL=$MIRBASEURL."servlets/solr/select";
$SEARCHBASEURL="http://esx-127.gbv.de:8081/solr/hagen/select";

$SOLRQUERY = "q=identifier.type.urn%3A*";
$SOLRPARMS = "&rows=4000&fl=id%2C+identifier.type.urn%2C+derivates&wt=json&indent=true";

$SolrULR=$SEARCHBASEURL."?".$SOLRQUERY.$SOLRPARMS;


function printhelp() {
?>
  urn2derivate - Verschiebt eine URN aus den Mods Objecten in das Derivate

   h        - help
   c        - clear data (import/)
   p <file> - Datei mit der Konfiguration

<?php
}
/********
* main
*********/

if (!file_exists('import')) {
  mkdir('import', 0777, true);
}

$ResponseString = file_get_contents ($SolrULR);

echo $SolrULR;
echo $ResponseString;

$Response = json_decode($ResponseString,true);
$Commands = "";
//print_r($Response);

foreach ($Response["response"]["docs"] as $Doc) {

  
  $DerivateID = $Doc["derivates"][0];
  $ObjectID = $Doc["id"];
  
  // Ermitteln der URN 
  $URN=$Doc["identifier.type.urn"][0];
  
  // Wenn es eine URN Gibt, dann trage sie in das Derivate ein
  if ($URN) {

    echo "Verschiebe URN (".$URN.")".$ObjectID."->".$DerivateID."\n";

    $Filename=$DerivateID.".xml";

    $Filepath="export/derivate/".$Filename;
 
    if (is_file ($Filepath)) {
      
      $XML = new DOMDocument();
      $XML->load($Filepath);
      $xpath = new DOMXpath($XML);
      $elements = $xpath->query("/mycorederivate/derivate/fileset");
      if ($elements->length>1) {
        die ("mehr als ein Fileset gefunden.");	
      }
      if ($elements->length==1) {
        $element=$elements->item(0);
        $element->setAttribute("urn",$URN);
      }
      if ($elements->length==0) {
        $Derivate=$XML->getElementsByTagName('derivate')->item(0);
        $Fileset=$XML->createElement( "fileset" );
        $Fileset->setAttribute("urn",$URN);
        $Derivate->appendChild($Fileset);
        $Maindoc = $xpath->query("/mycorederivate/derivate/internals/internal")->item(0)->getAttribute("maindoc");
        //$File=$XML->createElement( "file" );
        //$File->setAttribute("name",$Maindoc);
        //$Fileset->appendChild($File);
      } 
      
      $SaveFilepath="import/derivate/".$Filename;
      file_put_contents($SaveFilepath, $XML->saveXML());
      shell_exec("cp -r export/derivate/".$DerivateID." import/derivate/");
      $Commands.="delete derivate ".$DerivateID."\n"; 
    } else {
      echo "Error:Derivatedatei nicht gefunden.(".$Filename.")\n";
    }

    // Entferne die URN aus dem Mods Datensatz
    $Filename=$ObjectID.".xml";
    $Filepath="export/objects/".$Filename;

    if (is_file ($Filepath)) {
      
      $XML = new DOMDocument();
      $XML->load($Filepath);
      $xpath = new DOMXpath($XML);
      $xpath->registerNamespace('mods', "http://www.loc.gov/mods/v3");
      $IDs = $xpath->query("//mods:identifier[@type='urn']");
      foreach ($IDs as $ID) {
        if ($ID->nodeValue == $URN) {
          $Parent=$ID->parentNode;
          $Parent->removeChild($ID);
        } 
      }
      $SaveFilepath="import/objects/".$Filename;
      file_put_contents($SaveFilepath, $XML->saveXML());
      
    } else {
      echo "Error: mods-Objectdatei nicht gefunden.(".$Filename.") \n";
    }

    
  }
  
}
file_put_contents("commnands.txt",$Commands);
?>
Die Dateien liegen nun in import/

Nachfolgende Schritte:

- update der Derivate

- update der mods-Objekte
 
