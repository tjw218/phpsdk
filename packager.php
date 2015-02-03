<?php
require_once dirname(__FILE__) . '/build/Burgomaster.php';

//
// use burgomaster to package phar/zip files
// (https://github.com/mtdowling/Burgomaster)
//
// the "make build" command should have curl'd the file
//

$staging  = dirname(__FILE__) . '/build/staging';
$root     = dirname(__FILE__);
$packager = new \Burgomaster($staging, $root);

// basic text files
foreach (['README.md', 'LICENSE'] as $file) {
    $packager->deepCopy($file, $file);
}

// copy pmp core
$packager->recursiveCopy('src/Pmp', 'Pmp', ['php']);

// copy vendor'd libs
$packager->recursiveCopy('vendor/guzzle/guzzle/src/Guzzle', 'Guzzle', ['php', 'pem']);
$packager->recursiveCopy('vendor/symfony/event-dispatcher/Symfony', 'Symfony', ['php']);

// autoload the PMP entry point
$packager->createAutoloader(['Pmp/Sdk.php']);

// create archive (TODO: would a zip even be useful?)
$packager->createPhar("$root/build/pmpsdk.phar");
// $packager->createZip("$root/build/pmpsdk.zip");