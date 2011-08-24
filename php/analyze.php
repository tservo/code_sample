<?php

/**
 * analyze.php
 * main code for pulling in / analyzing flat files
 * by website
 * usage:  php analyze.php {list of files}
 * @author rbacon
 * @date 20080422
 */

// model 
include_once("parser.php");
// view
include_once("reporter.php");
 
//////////////////
// pull in list of filenames
// ? do we do checking here of whether files exist?
$files = $argv;
array_shift($files); // get rid of command name

///////////////////
// action: given list of filenames, load into parser
$parser = new Parser();

foreach($files as $file) {
    echo "Parsing $file\n";
    // did it work?
    if ($parser->parse($file) === false) {
        echo 'Error: ' . $parser->getError() . "\n";
    };

}

// analyze data

///////////////////
// view: print results of data
$reporter = new Reporter($parser->getData());

$reporter->display();



?>