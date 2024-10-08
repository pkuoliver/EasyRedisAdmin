<?php
//Copy this file to config.inc.php and make changes to that file to customize your configuration.

$config = array(
	'servers' => array(
		array(
			'name'   => 'local server', // Optional name.
			'host'   => '127.0.0.1',
			'port'   => 6379,
			'filter' => '*',
			'scheme' => 'tcp', // Optional. Connection scheme. 'tcp' - for TCP connection, 'unix' - for connection by unix domain socket
			'path'   => '', // Optional. Path to unix domain socket. Uses only if 'scheme' => 'unix'. Example: '/var/run/redis/redis.sock'

			// Optional Redis authentication.
			//'auth' => 'redispasswordhere' // Warning: The password is sent in plain-text to the Redis server.
		),

		/*array(
			'host' => 'localhost',
			'port' => 6380
		),*/

		/*array(
			'name'      => 'local db 2',
			'host'      => 'localhost',
			'port'      => 6379,
			'db'        => 1,             // Optional database number, see http://redis.io/commands/select
			'databases' => 1,             // Optional number of databases (prevents use of CONFIG command).
			'filter'    => 'something:*', // Show only parts of database for speed or security reasons.
			'seperator' => '/',           // Use a different seperator on this database (default uses config default).
			'flush'     => false,         // Set to true to enable the flushdb button for this instance.
			'charset'   => 'cp1251',      // Keys and values are stored in redis using this encoding (default utf-8).
			'keys'      => false,         // Use the old KEYS command instead of SCAN to fetch all keys for this server (default uses config default).
			'scansize'  => 1000           // How many entries to fetch using each SCAN command for this server (default uses config default).
		),*/
	),


	'seperator' => ':',


	// Uncomment to show less information and make EasyRedisAdmin fire less commands to the Redis server. 
	// Recommended for a really busy Redis server.
	'faster' => false,

	// Show detail size, if faster is false && show_detail_size is true, we will calc relatively accurate 
	// memory consumption for each key, or just estimate each key memory
	'show_detail_memory' => true,

	// Uncomment to enable HTTP authentication
	/*'login' => array(
		// Username => Password
		// Multiple combinations can be used
		'admin' => array(
			'password' => 'adminpassword',
		),
		'guest' => array(
			'password' => '',
			'servers'  => array(0) // Optional list of servers this user can access.
		)
	),*/

	// Use HTML form/cookie-based auth instead of HTTP Basic/Digest auth
	'cookie_auth' => false,


	/*'serialization' => array(
		'foo*' => array( // Match like KEYS
			// Function called when saving to redis.
			'save' => function($data) { return json_encode(json_decode($data)); },
			// Function called when loading from redis.
			'load' => function($data) { return json_encode(json_decode($data), JSON_PRETTY_PRINT); },
		),
	),*/


	// You can ignore settings below this point.

	'maxkeylen'           => 100,
	'count_elements_page' => 100,

	// Use the old KEYS command instead of SCAN to fetch all keys.
	'keys' => false,

	// How many entries to fetch using each SCAN command.
	'scansize' => 1000,

	// Max keys count to show, If your redis does not show up because there are too many keys, 
	// you can lower the value appropriately.
	// Default value is -1, means no limits.
	'max_keys_to_show' => -1,
);
