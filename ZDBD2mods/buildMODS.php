#!/usr/bin/php
<?php
/*
        Paul Borchert borchert@gbv.de
        28.3.2017

        


*/

if ($argc != 1 || in_array($argv[1], array('--help', '-help', '-h', '-?','help'))) {

?>
  Usage:
  ./build-mods.php or php -f build-mods.php



<?php
} else {

    $xslDoc = new DOMDocument();
    $xslDoc->load("lib/RDF-mods-journal.xsl");
    $proc = new XSLTProcessor();
    $proc->importStylesheet($xslDoc);
    $files = scandir('cache/');
    foreach($files as $file) {
        //do your work here
        $rdf=file_get_contents("cache/".$file);
        echo $rdf;
        $rdf_xml = new SimpleXMLElement($rdf);
        
        $mods=$proc->transformToXML($rdf_xml);

        $str='www.w3.org/1999/xlink" ID="'.$id.'" label="'.$id.'" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
        $str.=' version="2.0" xsi:noNamespaceSchemaLocation="datamodel-mods.xsd">';
        $mods=str_replace('www.w3.org/1999/xlink">',$str,$mods);
        
        file_put_contents("mods/".$id.".xml",$mods);
        
    } 
    

}
?>