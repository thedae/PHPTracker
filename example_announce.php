<?php

// ---------------------------------------
// This is how to set up an announce URL.
// ---------------------------------------

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
    // The IP address of the connecting client.
    'ip'        => $_SERVER['REMOTE_ADDR'],
    // Interval of the next announcement in seconds - sent back to the client.
    'interval'  => 60,
) );

// Core class managing the announcements.
$core = new PHPTracker_Core( $config );

// We take the parameters the client is sending and initialize a config
// object with them. Again, you can implement your own Config class to do this.
$get = new PHPTracker_Config_Simple( $_GET );

// We simply send back the results of the announce method to the client.
echo $core->announce( $get );