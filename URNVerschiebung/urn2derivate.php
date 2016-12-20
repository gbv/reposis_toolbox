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

$MIRBASEURL="http://esx-171.gbv.de/clausthal2/";

$SEARCHBASEURL=$MIRBASEURL."servlets/solr/select";
//$SEARCHBASEURL="http://esx-127.gbv.de:8081/solr/hagen/select";

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

  $ObjectID = $Doc["id"];
  
  // Ermitteln der URN 
  $URN=$Doc["identifier.type.urn"][0];
  
  // Wenn es eine URN Gibt, dann trage sie in das Derivate ein
  if ($URN) {
    echo "Verschiebe URN (".$URN.")".$ObjectID;
    if (! isset ($Doc["derivates"])){
      echo "-> kein Derivate gefunden.\n";
      continue;
    } else if (count ($Doc["derivates"]) == 1) {
      $DerivateID = $Doc["derivates"][0];
      echo "->".$DerivateID."\n";
    } else {
      echo "\n  mehr als ein derivate: ".count ($Doc["derivates"])."(".$ObjectID.")\n";
      $DerivateID="";
      $DerivateFilename="";
      foreach ($Doc["derivates"] as $derivate) {
        echo "   ".$derivate." -> ";
        $Filename=$derivate.".xml";
        $Filepath="export/derivate/".$Filename;
        if (is_file ($Filepath)) {
          $XML = new DOMDocument();
          $XML->load($Filepath);
          $xpath = new DOMXpath($XML);
          $elements = $xpath->query("/mycorederivate/derivate/internals/internal");
          if ($elements->length==1) {
            $element=$elements->item(0);
            $Maindoc=$element->getAttribute("maindoc");
            echo $Maindoc;
          } else { 
            die ("mehr als ein maindoc im derivate");
          }
          echo "\n";
          if (strlen($Maindoc) < strlen($DerivateFilename) || $DerivateFilename==""){
            $DerivateID=$derivate;
            $DerivateFilename=$Maindoc;
	  }
        }
      }
      echo "    -> ausgewaehlt:".$DerivateID."\n";
    }

    $Filename=$DerivateID.".xml";

    $Filepath="export/derivate/".$Filename;
 
    if (is_file ($Filepath)) {

      $copyderivate=true;      

      $XML = new DOMDocument();
      $XML->load($Filepath);
      $xpath = new DOMXpath($XML);
      $elements = $xpath->query("/mycorederivate/derivate/fileset");
      if ($elements->length>1) {
        die ("mehr als ein Fileset gefunden.");	
      }
      if ($elements->length==1) {
        $element=$elements->item(0);
        if ($element->getAttribute("urn")==$URN) {
          echo "  URN war schon im derivate gespeichert.\n";
          $copyderivate =false;
        } else { 
          echo "  ueberschreibe URN ".$element->getAttribute("urn")."mit ".$URN."\n";
          $element->setAttribute("urn",$URN);
        }
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
      if ($copyderivate) {
        $SaveFilepath="import/derivate/".$Filename;
        file_put_contents($SaveFilepath, $XML->saveXML());
        shell_exec("cp -r export/derivate/".$DerivateID." import/derivate/");
        $Commands.="delete derivate ".$DerivateID."\n"; 
      }
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
file_put_contents("commands.txt",$Commands);
?>
Die Dateien liegen nun in import/

Nachfolgende Schritte:

- update der mods-Objekte
  mir.sh update all objects from directory import/objects/

- loeschen der Derivate mit Hilfe der Datei commands.txt
  mir.sh process commands.txt

- neu laden der Derivate
  mir.sh load all derivates from directory import/derivate
 
