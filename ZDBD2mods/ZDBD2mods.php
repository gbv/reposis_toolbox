#!/usr/bin/php
<?php

$import = fopen('php://stdin', 'r');

$journals = simplexml_load_file('php://stdin');

foreach ($journals as $journal) {
  echo $journal->title;
  echo "\n";
}

?>