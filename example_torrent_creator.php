<?php

// -----------------------------------------------------------
// This is how to create a .torrent file from a physical file.
// -----------------------------------------------------------

// Registering autoloader, essential to use the library.
require( dirname(__FILE__).'/lib/PHPTracker/Autoloader.php' );
PHPTracker_Autoloader::register();

// Creating a simple config object. You can replace this with your object
// implementing PHPTracker_Config_Interface.
$config = new PHPTracker_Config_Simple( array(
    // Persistense object implementing PHPTracker_Persistence_Interface.
    // We use MySQL here. The object is initialized with its own config.
    'persistence' => new PHPTracker_Persistence_Mysql(
        new PHPTracker_Config_Simple( array(
            'db_host'       => 'localhost',
            'db_user'       => 'misc',
            'db_password'   => 'misc',
            'db_name'       => 'misc',
        ) )
    ),
    // List of public announce URLs on your server.
    'announce'  => array(
        'http://php-tracker.dev/example_announce.php',
    ),
) );

// Core class managing creating the file.
$core = new PHPTracker_Core( $config );

// Setting appropiate HTTP header and sending back the .torrrent file.
// This is VERY inefficient to do! SAVE the .torrent file on your server and
// serve the saved copy!
header( 'Content-Type: application/x-bittorrent' );
header( 'Content-Disposition: attachment; filename="test.torrent"' );

// The first parameters is a path (can be absolute) of the file,
// the second is the piece size in bytes.
echo $core->createTorrent( '../test.avi', 524288 );