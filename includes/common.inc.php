<?php

define('EASY_REDIS_ADMIN_PATH', dirname(__DIR__));

// Undo magic quotes (both in keys and values)
if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
	$process = array(&$_GET, &$_POST);

	foreach ($process as $key => $val) {
		foreach ($val as $k => $v) {
			unset($process[$key][$k]);

			if (is_array($v)) {
				$process[$key][stripslashes($k)] = $v;
				$process[] = &$process[$key][stripslashes($k)];
			} else {
				$process[$key][stripslashes($k)] = stripslashes($v);
			}
		}
	}

	unset($process);
}


// These includes are needed by each script.
if(file_exists(EASY_REDIS_ADMIN_PATH . '/config.php')){
	require_once EASY_REDIS_ADMIN_PATH . '/config.php';
}else{
	require_once EASY_REDIS_ADMIN_PATH . '/config.sample.php';
}
require_once EASY_REDIS_ADMIN_PATH . '/includes/functions.inc.php';
require_once EASY_REDIS_ADMIN_PATH . '/includes/page.inc.php';

if (isset($config['login'])) {
	require_once EASY_REDIS_ADMIN_PATH . '/includes/login.inc.php';
}


if (isset($login['servers'])) {
	$i = current($login['servers']);
} else {
	$i = 0;
}


if (isset($_GET['s']) && is_numeric($_GET['s']) && ($_GET['s'] < count($config['servers']))) {
	$i = $_GET['s'];
}

$server            = $config['servers'][$i];
$server['id']      = $i;
$server['charset'] = isset($server['charset']) && $server['charset'] ? $server['charset'] : false;


mb_internal_encoding('utf-8');


if (isset($login, $login['servers'])) {
	if (array_search($i, $login['servers']) === false) {
		die('You are not allowed to access this database.');
	}

	foreach ($config['servers'] as $key => $ignore) {
		if (array_search($key, $login['servers']) === false) {
			unset($config['servers'][$key]);
		}
	}
}


if (!isset($server['db'])) {
	if (isset($_GET['d']) && is_numeric($_GET['d'])) {
		$server['db'] = $_GET['d'];
	} else {
		$server['db'] = 0;
	}
}


if (!isset($server['filter'])) {
	$server['filter'] = '*';
}

// filter from GET param
if (isset($_GET['filter']) && $_GET['filter'] != '') {
	$server['filter'] = $_GET['filter'];
	if (strpos($server['filter'], '*') === false) {
		$server['filter'].= '*';
	}
}

if (!isset($server['seperator'])) {
	$server['seperator'] = $config['seperator'];
}

if (!isset($server['keys'])) {
	$server['keys'] = $config['keys'];
}

if (!isset($server['scansize'])) {
	$server['scansize'] = $config['scansize'];
}

if (!isset($server['serialization'])) {
	if (isset($config['serialization'])) {
		$server['serialization'] = $config['serialization'];
	}
}

// Setup a connection to Redis.
try {
	$redisHost = '127.0.0.1';
	$redisPort = '6379';
	$redis = new Redis();
	if(!$server['port']) {
		$redisHost = $server['host'];
	} else {
		$redisHost = $server['host'];
		$redisPort = $server['port'];
	}
	$redis->connect($redisHost, $redisPort);
} catch (Exception $exception) {
	die('ERROR: ' . $exception->getMessage());
}

if (isset($server['auth'])) {
	if (!$redis->auth($server['auth'])) {
		die('ERROR: Authentication failed ('.$server['host'].':'.$server['port'].')');
	}
}


if ($server['db'] != 0) {
	if (!$redis->select($server['db'])) {
		die('ERROR: Selecting database failed ('.$server['host'].':'.$server['port'].','.$server['db'].')');
	}
}

?>
