<?php

// --------------------------------------
// This is how to start a seeding server.
// --------------------------------------

// [!] Run this file in CLI only!
// /usr/bin/php example_seeder.php

// Registering autoloader, essential to use the library.
require( dirname(__FILE__).'/lib/PHPTracker/Autoloader.php' );
PHPTracker_Autoloader::register();

// Persistense object implementing PHPTracker_Persistence_Interface.
// We use MySQL here. The object is initialized with its own config.
$persistence = new PHPTracker_Persistence_Mysql(
    new PHPTracker_Config_Simple( array(
        'db_host'       => '192.168.1.100',
        'db_user'       => 'misc',
        'db_password'   => 'misc',
        'db_name'       => 'misc',
    ) )
);

// Setting up seeder peer. This will listen to connections and serve files.
$peer = new PHPTracker_Seeder_Peer(
    new PHPTracker_Config_Simple( array(
        'persistence'           => $persistence,
        // PUBLIC address of the seeder server. This will be used fr announcements (ie. sent to the clients).
        'seeder_address'        => '192.168.1.100',
        'seeder_port'           => 6881,
        // Number telling how many processes should be forked to listen to incoming connections.
        'peer_forks'            => 10,
        // If specified, gives a number of outsider seeders to make self-seeding stop.
        // This saves you bandwidth - once your file is seeded by others, you can stop serving it.
        // Number of seeders is permanently checked, but probably 1 is too few if you want your file to be available always.
        'seeders_stop_seeding'  => 5,
    )
) );

// We set up a seeding server which starts the seeding peer, and makes regular
// announcements to the database adding itself to the peer list for all
// active torrents.
$server = new PHPTracker_Seeder_Server(
     new PHPTracker_Config_Simple( array(
        'persistence'           => $persistence,
        'peer'                  => $peer,
    )
) );

$server->start();