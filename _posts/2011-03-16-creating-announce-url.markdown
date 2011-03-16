---
layout: default
title: Creating announce URL
description: Learn how to set up announce (tracker) URL easily to track your peer announcements and send them peer lists. Code examples, description.
---
## {{ page.title }} ##

Once your clients downloaded the .torrent file representing your original file (see "Creating torrent files") you will have to provide them with an interface to announce that they are online and obtain the list of other peers online.  
  
This interface is the announce URL (or multiple announce URLs) that you encoded in your .torrent file. Clients will make regular GET requests to this URL while they are online telling your server the status of their download and asking for the list of other peers.  
  
With PHPTracker it is very easy to set up an announce URL. You will have to use the same database profile that you used while creating .torrent file.  
  
Here is an example code:

{% highlight php %}
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
			'db_host'       => '192.168.1.100',
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
{% endhighlight %}