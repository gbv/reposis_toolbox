<?php

function downloadRDFbyZDBDID ($zdbdid) {
    $file="cache/".$zdbd_id.".rdf";
    $rdf=false;
    if (!file_exists($file)) {
    
        $url="http://ld.zdb-services.de/data/".$zdbdid.".rdf";
        $url_sru="http://services.d-nb.de/sru/zdb?version=1.1&operation=searchRetrieve&query=zdbid%3D".$zdbdid."&recordSchema=RDFxml";
    
        $rdf=file_get_contents($url);
        if ($rdf === false) {
            echo "SRU\n";
            $rdf=file_get_contents ($url_sru);
            $rdf=str_replace("\n", " ", $rdf);
            $treffer = preg_match ('/<rdf:RDF.*>.*<\/rdf:RDF>/',$rdf,$matches);
            $rdf=$matches[0];
            if ($rdf!="") {
                $rdf = '<?xml version="1.0" encoding="UTF-8"?>'.$rdf;
            } else {
                return false;
            }
        }
        
        file_put_contents($file,$rdf);
    } else {
        return true;
    }
    
    return true;
}


?>