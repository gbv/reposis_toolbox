Mit diesem Tool sollen mods-Objecte von Zeitschriften erzeugt werden, die dann in das
Repository importiert werden können. Dazu wird eine Liste mit Zeitschriften übergeben,
an Hand von der per SRU die Zeitschriften in der ZDB ermittelt  und anschließen 
nach mods umgewandelt werden.

rm cache/*
rm mods/*

./example/CSV_ZDBD_Import/wiasCSV.php | ./collectZdbRDF.php

./buildMODS.php

./transliterateUTF8.sh