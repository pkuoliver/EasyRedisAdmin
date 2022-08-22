<?php
require_once 'includes/common.inc.php';

$info = array();

foreach ($config['servers'] as $i => $server) {
	if (!isset($server['db'])) {
			$server['db'] = 0;
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
		$redis = false;
	}

	if(!$redis) {
		$info[$i] = false;
	} else {
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

		$info[$i]         = $redis->info();
		$info[$i]['size'] = $redis->dbSize();
		$info[$i]['host'] = $server['host'];
	}
}

$page['css'][] = 'frame';
$page['js'][]  = 'frame';

require 'includes/header.inc.php';

?>

<?php foreach ($config['servers'] as $i => $server) { ?>
	<div class="server">
		<h2><?php echo isset($server['name']) ? format_html($server['name']) : format_html($server['host'])?></h2>

		<?php if(!$info[$i]): ?>
		<div style="text-align:center;color:red">Server Down</div>
		<?php else: ?>

		<table>
			<tr><td><div>Host:</div></td><td><div><?php echo $info[$i]['host']?></div></td></tr>
			<tr><td><div>Port:</div></td><td><div><?php echo $info[$i]['tcp_port']?></div></td></tr>
			<tr><td><div>Keys:</div></td><td><div><?php echo $info[$i]['size']?></div></td></tr>
			<tr><td><div>Clients:</div></td><td><div><?php echo $info[$i]['connected_clients']?></div></td></tr>
			<tr><td><div>Mem/Ratio</div></td><td><div><?php echo format_size($info[$i]['used_memory']) . '/' . $info[$i]['mem_fragmentation_ratio']?></div></td></tr>
			<tr><td><div>Version:</div></td><td><div><?php echo $info[$i]['redis_version']?></div></td></tr>
			<tr><td><div>Uptime:</div></td><td><div><?php echo format_time($info[$i]['uptime_in_seconds'])?></div></td></tr>
			<tr><td><div style="min-width:70px;">Last save:</div></td><td><div style="min-width:100px;">
			<?php
				if (isset($info[$i]['rdb_last_save_time'])) {
					if((time() - $info[$i]['rdb_last_save_time'] ) >= 0) {
						echo format_time(time() - $info[$i]['rdb_last_save_time']) . " ago";
					} else {
						echo format_time(-(time() - $info[$i]['rdb_last_save_time'])) . "in the future";
					}
				} else {
					echo 'never';
				}
			?>
			</div></td></tr>
			<tr><td><div>Operation:</div></td><td><div>
				<a href="save.php?s=<?php echo $i?>"><img src="images/save.png" width="16" height="16" title="Save Now" alt="[S]" class="imgbut"></a> | 
				<a href="info.php?s=<?php echo $i?>"><img src="images/info.png" width="16" height="16" title="Redis Info" alt="[S]" class="imgbut"></a>
			</div></td></tr>
		</table>
		<?php endif; ?>
	</div>
<?php } ?>

<p class="clear">
<a href="https://github.com/pkuoliver/EasyRedisAdmin" target="_blank">EasyRedisAdmin on GitHub</a>
</p>
<?php
require 'includes/footer.inc.php';
?>
