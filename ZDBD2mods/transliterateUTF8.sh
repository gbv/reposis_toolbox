#!/bin/bash

for filename in mods/*.xml; do
    uconv -x any-nfc "$filename" > "$filename".tmp
    mv "$filename".tmp "$filename"
done