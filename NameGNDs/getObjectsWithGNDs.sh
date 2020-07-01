#/bin/bash
MIRSolr=https://www.openagrar.de/servlets/solr/select?
URL=$MIRSolr"?core=main&q=mods.nameIdentifier%3Agnd*+AND+objectType%3Amods&rows=100000&fl=id%2Cmods.nameIdentifier&wt=json&sort=id+asc"

wget -O ObjectsWithGNDs.json $URL 
