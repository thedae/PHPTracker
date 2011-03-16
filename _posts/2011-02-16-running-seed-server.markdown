---
layout: default
title: Running seed server
description: Tutorial how to run seeder server daemon with easily. Code examples, description.
---
## {{ page.title }} ##

Now that you created your .torrent file and your announce URL is listening (see "Creating torren files" and "Creating announce URL") your file still can't be downloaded. Why?  
  
Because there is no one out there having a full copy of the file (to be seeder), so other peers cannot download it from anywhere. So what are you supposed to do? You have to be the first seeder of your file.   
  
So you want to save bandwidth? Don't worry, you only have to serve the file while there are no external seeders for it. PHPTracker automatically takes care of stopping seeding (and eventually restarting) when you have enough seeders out there.  
  
To start seeding your file, you'll have to run a seeding server daemon. PHPTracker provides you with a pure PHP, multi-process seeding server.  
  
Because PHPTracker uses process forking, it can only run on POSIX servers (eg. Linux). Obviously, the seeder server has to run on the same server where you have your own copy of your original files, which, of course, can be stored in a private location.

When you start running your seeder server process, it will create two child process branches of itself. The first child process will listen to incoming connections and take care of serving files, and the second child process will constantly emulate announcements to your database for your client peers to know the address of your seeding server.  
  
The file serving process will fork itself to an arbitrary number of child processes to be able to serve multiple clients at once. The number of forks is constantly monitored and maintained in case one process fails. Be careful with adjusting the number of forked peer processes!  
  
Just like creating torrent files and setting up announce URL, seeding needs to have access to the cental data source.  
  
Remember, that you have to use PHP command line interface to start the daemon.

{% highlight bash %}
$ php example_seeder.php
{% endhighlight %}

Here is a simple example how to run your seeding server.

{% highlight php %}
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
		'db_host'       => 'localhost',
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
		'seeder_address'        => '192.168.2.100',
		'seeder_port'           => 6881,
		// Number telling how many processes should be forked to listen to incoming connections.
		'peer_forks'            => 10,
		// If specified, gives a number of outsider seeders to make self-seeding stop.
		// This saves you bandwidth - once your file is seeded by others, you can stop serving it.
		// Number of seeders is permanently checked, but probably 1 is too few if you want your file to be available always.
		'seeders_stop_seeding'  => 5,
		// Intializing file logger with default file path (/var/log/phptracker.log).
		// File logger might accept config object with keys file_path_messages
		// and file_path_errors as absolute path of log files for messages and
		// errors respectively.
		'logger'  => new PHPTracker_Logger_File(),
	)
) );

// We set up a seeding server which starts the seeding peer, and makes regular
// announcements to the database adding itself to the peer list for all
// active torrents.
$server = new PHPTracker_Seeder_Server(
	 new PHPTracker_Config_Simple( array(
		'persistence'           => $persistence,
		'peer'                  => $peer,
		 // Intializing file logger with default file path (/var/log/phptracker.log).
		'logger'  => new PHPTracker_Logger_File(),
	)
) );

// Starting "detached" means that process will unrelate from terminal and run as deamon.
// To run in terminal, you can use start().
// Detached running requires php-posix.
$server->startDetached();
{% endhighlight %}