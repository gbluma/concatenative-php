#!/usr/bin/env php
<?php 

require_once("src/Concatenative/language.php");

// start repl
echo <<<HERE

Concatenative-PHP  Copyright (C) 2013  Garrett Bluma
This program comes with ABSOLUTELY NO WARRANTY; for details read LICENSE.md.
This is free software, and you are welcome to redistribute it.


HERE;

while(true) {
    echo ">>> ";
    $handle = fopen ("php://stdin","r");
    $line = fgets($handle);
    try {
        Concatenative\read($line);
        if (count($stack) > 0) Concatenative\read(".stack");
        else echo "\n";
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}
