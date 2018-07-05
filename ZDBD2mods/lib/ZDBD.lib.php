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
        logging ( "  Found in cache:".$file."\n");
        return true;
    }
    
    return true;
}

function getRecordsByISSN (& $records , $issn) {
    $url_sru="http://services.d-nb.de/sru/zdb?version=1.1&operation=searchRetrieve&query=iss%3D".$issn."&recordSchema=RDFxml";
    $rdf=file_get_contents ($url_sru);
    if ($rdf) { 
        $rdf = trim(preg_replace('/\s+/', ' ', $rdf));
        if (preg_match_all('/<record>.*?<\/record>/', $rdf, $match)) {
            //print_r ($match);
            foreach ($match[0] as $record) {
                if (!preg_match('/'.$issn.'/i', $record,$match2)) {
                    continue;
                }
                array_push($records, $record);
            }
        }
    }
}

function getZDBIDfromRecord($record) {
    if (preg_match('/<rdf:Description rdf:about=\"http:\/\/ld.zdb-services.de\/resource\/(.+?)\">/', $record, $match2)) {
        return($match2[1]);
    }
    return null;
} 

function filterRecordsByTitle($records,$title) {
    logging ("    get ".count($records)." records try to filter by Title \n");
    $result=array();
    for ($i=0; $i < count($records); $i++) {
        $regex = '/'.$title.'/i';
        echo  $regex."\n";
        if (preg_match($regex,$records[$i],$match)) {
            array_push($result,$records[$i]);
        } else {
            echo "filter\n";
        }
    }
    logging ("    ... ".count($records)." records left \n");
    return ($result);
}

function filterRecordsByTitle2($records,$title) {
    logging ("    get ".count($records)." records try to filter by Title 2\n");
    $result=array();
    for ($i=0; $i < count($records); $i++) {
        $regex = '/<dc:title rdf:datatype=\"http\:\/\/www.w3.org\/2001\/XMLSchema#string\">'.$title.'<\/dc:title>/i';
        echo  $regex."\n";
        if (preg_match($regex,$records[$i],$match)) {
            array_push($result,$records[$i]);
        } else {
            echo "filter\n";
        }
    }
    logging ("    ... ".count($records)." records left \n");
    return ($result);
}


function getUniqueZDBIDsFromRecords($records) {
    $zdbdids = array();
    foreach ($records as $record) {
        array_push($zdbdids,getZDBIDfromRecord($record));
    }
    return(array_unique($zdbdids));
}

function downloadRDFbyISSN($issn_print,$issn_online,$title) {
    
    $records = array();
    getRecordsByISSN($records,$issn_print);
    getRecordsByISSN($records,$issn_online);
    
    $zdbdids = array();
    $filtRecords = $records;
    $zdbdids = getUniqueZDBIDsFromRecords($filtRecords);
    
    if ( (count($zdbdids) == 0)  || (2 < count($zdbdids))) $filtRecords=filterRecordsByTitle($records,$title);
    $zdbdids = getUniqueZDBIDsFromRecords($filtRecords);
        
    if ( (count($zdbdids) == 0)  || (2 < count($zdbdids))) $filtRecords=filterRecordsByTitle2($records,$title);
    $zdbdids = getUniqueZDBIDsFromRecords($filtRecords);
    
    if ((count($zdbdids) == 0)  || (2 < count($zdbdids))) {
        logging ( "    Fail: get ".count($zdbdids)." records \n");
        //logging ( var_export($zdbdids, true));
        return false;
    }
    
    $success=true;
    foreach ($zdbdids as $zdbdid) {
        $success = $success && downloadRDFbyZDBDID($zdbdid);
    }
    return ($success);
}

function getZDBIDbyTitle (& $zdbdids , $title) {
    $url_sru="http://services.d-nb.de/sru/zdb?version=1.1&operation=searchRetrieve&query=tst%3D".rawurlencode($title)."&recordSchema=RDFxml";
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
        logging ( "    Fail: get ".count($zdbdids)." records \n");
        return false;
    }
    
    $success=true;
    foreach ($zdbdids as $zdbdid) {
        $success = $success && downloadRDFbyZDBDID($zdbdid);
    }
    return ($success);
}

?>
