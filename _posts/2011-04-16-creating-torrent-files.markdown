---
layout: default
title: Creating torrent files
description: How to create .torrent files out of files on your server and provide bittorrent download to your users. Code examples, description.
---
## {{ page.title }} ##

Before you start serving your files via Bittorrent, you need to create a metainformation file that contains all the data that the clients need to know about the content of the file and how to get it. This file has the .torrent extension.  
  
The file internally uses Bencode format. PHPTracker comes with its own Bencode encoder/decoder library, so you can create your files very easily.  
  
First of all, you'll need to have a persistent storage (database) to store all the information of the torrents you create. This storage will be used while the client peers are announcing themselves and seeder server also uses this as its data source. PHPTracker is shipped with MySQL persistence object so if you have a MySQL database set up, you don't need to worry.  
  
If you prefer your own persistence solution you can implement the persistence interface of PHPTracker and inject your compatible persistence object instead of MySQL.  
  
Once you created a .torrent file, it's highly recommended to save it to the disk and serve it from there. Do not create .torrent files on the fly every time a user clicks a link on your website, because if you do so, PHPTracker will read and hash your origin file every time. You only have to run torrent creation again when your origin file changes.  
  
For your clients to be able to download the original file, you'll have to provide an announce URL in the .torrent file. This is a public URL on your server that takes care of tracking online peers. See "Creating announce URL".  
  
Here is a simple example how to create torrent files:  
  
{% highlight php %}
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
			'db_host'       => '192.168.1.100',
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
echo $core->createTorrent( 'netbeans.exe', 524288 );
{% endhighlight %}