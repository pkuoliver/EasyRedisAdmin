<?php

function format_html($str) {
	global $server;

	if (isset($server['charset']) && $server['charset']) {
		$res = mb_convert_encoding($str, 'utf-8', $server['charset']);
	} else {
		$res = $str;
	}

	$res = htmlentities($res, defined('ENT_SUBSTITUTE') ? (ENT_QUOTES | ENT_SUBSTITUTE) : ENT_QUOTES, 'utf-8');

	return ($res || !$str) ? $res :  '(' . strlen($str) . ' bytes)';
}


function input_convert($str) {
	global $server;

	if (isset($server['charset']) && $server['charset']) {
		return mb_convert_encoding($str, $server['charset'], 'utf-8');
	} else {
		return $str;
	}
}


function format_time($time, $ago = false) {
	$minute = 60;
	$hour   = $minute * 60;
	$day    = $hour   * 24;

	$when = $time;

	if ($when >= 0)
		$suffix = 'ago';
	else {
		$when = -$when;
		$suffix = 'in the future';
	}

	if ($when > $day) {
		$when = round($when / $day);
		$what = 'day';
	} else if ($when > $hour) {
		$when = round($when / $hour);
		$what = 'hour';
	} else if ($when > $minute) {
		$when = round($when / $minute);
		$what = 'minute';
	} else {
		$what = 'second';
	}

	if ($when != 1) $what .= 's';

	if ($ago) {
		return "$when $what $suffix";
	} else {
		return "$when $what";
	}
}


function format_size($size) {
	$sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

	if ($size == 0) {
		return '0 B';
	} else {
		return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2).' '.$sizes[$i];
	}
}


function format_ttl($seconds) {
	if ($seconds > 60) {
		return sprintf('%d (%s)', $seconds, format_time($seconds));
	} else {
		return $seconds;
	}
}


function str_rand($length) {
	$r = '';

	for (; $length > 0; --$length) {
		$r .= chr(rand(32, 126)); // 32 - 126 is the printable ascii range
	}

	return $r;
}


function encodeOrDecode($action, $key, $data) {
	global $server;

	if (isset($_GET['raw']) || !isset($server['serialization'])) {
		return $data;
	}

	foreach ($server['serialization'] as $pattern => $closures) {
		if (fnmatch($pattern, $key)) {
			return $closures[$action]($data);
		}
	}

	return $data;
}

function getRelativePath($base) {
	return substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $base));
}
