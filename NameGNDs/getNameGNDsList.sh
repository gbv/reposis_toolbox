#!/bin/bash

wget https://data.dnb.de/opendata/authorities-name_lds.ttl.gz
gzip -d authorities-name_lds.ttl.gz
grep UndifferentiatedPerson authorities-name_lds.ttl |  sed -e 's/<https:\/\/d-nb.info\/gnd\/\(.*\)> a gndo:UndifferentiatedPerson;/\1/' > NameGNDs.lst
