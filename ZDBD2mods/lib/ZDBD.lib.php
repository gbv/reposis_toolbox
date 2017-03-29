<?php

function downloadRDFbyZDBDID ($zdbdid) {
    $file="cache/".$zdbdid.".rdf";
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
        logging ( "  Download:".$file."\n");
        file_put_contents($file,$rdf);
    } else {
        return true;
    }
    
    return true;
}

function getZDBIDbyISSN (& $zdbdids , $issn) {
    $url_sru="http://services.d-nb.de/sru/zdb?version=1.1&operation=searchRetrieve&query=iss%3D".$issn."&recordSchema=RDFxml";
    $rdf=file_get_contents ($url_sru);
    if ($rdf) {
        if (preg_match_all('/<rdf:Description rdf:about=\"http:\/\/ld.zdb-services.de\/resource\/(.+)\">/', $rdf, $match)) {
            foreach ($match[1] as $zdbd_id) {
                array_push($zdbdids, $zdbd_id);
            }
        }
    }
}

function downloadRDFbyISSN($issn_print,$issn_online) {
    $zdbdids = array();
    
    getZDBIDbyISSN($zdbdids,$issn_print);
    getZDBIDbyISSN($zdbdids,$issn_online);
    
    $zdbdids = array_unique($zdbdids);
    
    if ((count($zdbdids) == 0)  || (2 < count($zdbdids))) {
        logging ( "  Fail: search results in ".count($zdbdids)." zdbdids");
        return false;
    }
    
    $success=true;
    foreach ($zdbdids as $zdbdid) {
        $success = $success && downloadRDFbyZDBDID($zdbdid);
    }
    return ($success);
}

function getZDBIDbyTitle (& $zdbdids , $title) {
    $url_sru="http://services.d-nb.de/sru/zdb?version=1.1&operation=searchRetrieve&query=tit%3D".$title."&recordSchema=RDFxml";
    $rdf=file_get_contents ($url_sru);
    if ($rdf) {
        if (preg_match_all('/<rdf:Description rdf:about=\"http:\/\/ld.zdb-services.de\/resource\/(.+)\">/', $rdf, $match)) {
            foreach ($match[1] as $zdbd_id) {
                array_push($zdbdids, $zdbd_id);
            }
        }
    }
}

function downloadRDFbyTitle($title) {
    $zdbdids = array();
    
    getZDBIDbyTitle($zdbdids,$title);
    
    $zdbdids = array_unique($zdbdids);
    
    if ((count($zdbdids) == 0)  || (2 < count($zdbdids))) {
        logging ( "   Fail: search results in ".count($zdbdids)." zdbdids");
        return false;
    }
    
    $success=true;
    foreach ($zdbdids as $zdbdid) {
        $success = $success && downloadRDFbyZDBDID($zdbdid);
    }
    return ($success);
}

?>