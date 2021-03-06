#!/usr/bin/php
<?php
/*
        Paul Borchert borchert@gbv.de
        28.3.2017

        


*/
$prefix = "zimport";

if ($argc > 2 || in_array($argv[1], array('--help', '-help', '-h', '-?','help'))) {

?>
  Usage:
  ./build-mods.php or php -f build-mods.php

  ./build-mods.php <prefix>

<?php
} else {
    if ($argc == 2) {
        $prefix = $argv[1];
    }
    $xslDoc = new DOMDocument();
    $xslDoc->load("lib/RDF-mods-journal-ubo.xsl");
    $proc = new XSLTProcessor();
    if ( ! $proc->importStylesheet($xslDoc)) {
        die ("Error while creating XSLTProcessor\n");
    }
    $files = glob("cache/*.rdf");
    $count=0;
    foreach($files as $file) {
        
        do {
            $count++;
            $id = $prefix."_mods_".sprintf('%08d', $count);
            $modsfile = "mods/".$id.".xml";
        } while (is_file($modsfile));
        
        $rdf = file_get_contents($file);
        $rdf_xml = new SimpleXMLElement($rdf);
        $mods=$proc->transformToXML($rdf_xml);
        
        $str='www.w3.org/1999/xlink" ID="'.$id.'" label="'.$id.'" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
        $str.=' version="2.0" xsi:noNamespaceSchemaLocation="datamodel-mods.xsd">';
        $mods=str_replace('www.w3.org/1999/xlink">',$str,$mods);
        
        file_put_contents($modsfile,$mods);
        
    } 
    

}
?>
