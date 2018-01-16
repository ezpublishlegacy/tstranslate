<?php

$Module = array( 'name' => 'tstranslate' );

$ViewList = array();
$ViewList['set'] = array( 'script' => 'set.php',
                          'functions' => array( 'write' ),
                          'default_navigation_part' => 'ezsetupnavigationpart',
                          'params' => array() );

$FunctionList = array();
$FunctionList['write'] = array();

?>