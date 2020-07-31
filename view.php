<?php
require_once 'includes/common.inc.php';

$page['css'][] = 'frame';
$page['css'][] = 'view';
$page['js'][]  = 'frame';

require 'includes/header.inc.php';

if (!isset($_GET['key'])) {
	?>
	Invalid key
	<?php

	require 'includes/footer.inc.php';
	die;
}
$key = $_GET['key'];
$typeMap = array(0=>'nont', 1=>'string', 2=>'set', 3=>'list', 4=>'zset', 5=>'hash');
$type   = $typeMap[$redis->type($key)];
$exists = $redis->exists($key);
$count_elements_page = isset($config['count_elements_page']) ? $config['count_elements_page'] : false;
$page_num_request    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page_num_request    = $page_num_request === 0 ? 1 : $page_num_request;

?>
<h2>Key: <?php echo format_html($key)?>
<?php if ($exists) { ?>
	<a href="rename.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;key=<?php echo urlencode($key)?>"><img src="images/edit.png" width="16" height="16" title="Rename" alt="[R]"></a>
	<a href="delete.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;key=<?php echo urlencode($key)?>" class="delkey"><img src="images/delete.png" width="16" height="16" title="Delete" alt="[X]"></a>
	<a href="export.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;key=<?php echo urlencode($key)?>"><img src="images/export.png" width="16" height="16" title="Export" alt="[E]"></a>
<?php } ?>
</h2>
<?php

if (!$exists) {
	?>
	This key does not exist.
	<?php

	require 'includes/footer.inc.php';
	die;
}


$alt      = false;
$ttl      = $redis->ttl($key);
$memory   = 0;
try {
	$encoding = $redis->object('encoding', $key);
} catch (Exception $e) {
	$encoding = null;
}


switch ($type) {
	case 'string':
		$value = $redis->get($key);
		$value = encodeOrDecode('load', $key, $value);
		$size  = strlen($value);

		$memory = 40 + strlen($key) + $redis->strLen($key);
		break;
	case 'hash':
		$values = $redis->hGetAll($key);
		$entrySize = 0;
		foreach ($values as $k => $value) {
			$values[$k] = encodeOrDecode('load', $key, $value);

			// dictEntry size
			$entrySize = $entrySize + 24 + strlen($k) + strlen($value);
		}
		$size = count($values);
		krsort($values);
		
		//        $keyMem             + dicthtMem + bucketMem            + dictEntryMem
		$memory = 40 + 2*strlen($key) + 4*8       + 8*calcMaxSize($size) + $entrySize;
		break;
	case 'list':
		$size = $redis->lLen($key);
		$values = $redis->lRange($key, 0, -1);
		$valSize = 0;
		foreach ($values as $k => $value) {
			$valSize = $valSize + (40 + 2*strlen($value));
		}
		
		$memory = 40 + 2*strlen($key) + $valSize;
		break;
	case 'set':
		$values = $redis->sMembers($key);
		$valSize = 0;
		foreach ($values as $k => $value) {
			$values[$k] = encodeOrDecode('load', $key, $value);
			$valSize = $valSize + 40 + 2*strlen($value);
		}
		$size = count($values);
		sort($values);

		$memory = 40 + 2*strlen($key) + $valSize;
		break;
	case 'zset':
		$values = $redis->zRevRange($key, 0, -1);
		$valSize = 0;
		foreach ($values as $k => $value) {
			$values[$k] = encodeOrDecode('load', $key, $value);
			$valSize = $valSize + (40 + 2*strlen($value)) + (40 + 8);
		}
		$size = count($values);

		$memory = 40 + 2*strlen($key) + $valSize;
		break;
}
	
if (isset($values) && ($count_elements_page !== false)) {
	$values = array_slice($values, $count_elements_page * ($page_num_request - 1), $count_elements_page,true);
}

?>

<div class="key-summary">
	<span class="button btn-success">Type: <?php echo format_html($type)?></span>
	<span class="button btn-danger">
		<abbr title="Time To Live">TTL</abbr>:<?php echo ($ttl == -1) ? 'does not expire' : format_ttl($ttl) ?> 
		<a href="ttl.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;key=<?php echo urlencode($key)?>&amp;ttl=<?php echo $ttl?>">
			<img src="images/edit.png" width="16" height="16" title="Edit TTL" alt="[E]" class="imgbut">
		</a>
	</span>

	<?php if (!is_null($encoding)) { ?>
	<span class="button btn-info">Encoding: <?php echo format_html($encoding)?></span>
	<?php } ?>
	<span class="button btn-primary">Size: <?php echo $size?> <?php echo ($type == 'string') ? 'characters' : 'items'?></span>
	<span class="button btn-primary">Memory: <?php echo format_size($memory);?></span>
</div>

<h2>Content:</h2>

<?php

// Build pagination div.
if (($count_elements_page !== false) && in_array($type, array('hash', 'list', 'set', 'zset')) && ($size > $count_elements_page)) {
	$prev       = $page_num_request - 1;
	$next       = $page_num_request + 1;
	$lastpage   = ceil($size / $count_elements_page);
	$lpm1       = $lastpage - 1;
	$adjacents  = 3;
	$pagination = '<div style="width: 50%; word-wrap: break-word;">';
	$url        = preg_replace('/&page=(\d+)/i', '', $_SERVER['REQUEST_URI']);

	if ($page_num_request > 1) $pagination .= "<a href=\"$url&page=$prev\">&#8592;</a>&nbsp;"; 
	else $pagination .= "&#8592;&nbsp;";

	if ($lastpage < 7 + ($adjacents * 2)) { //not enough pages to bother breaking it up
		for ($counter = 1; $counter <= $lastpage; $counter++) {
			if ($counter == $page_num_request) $pagination .= $page_num_request . '&nbsp;'; 
			else $pagination .= "<a href=\"$url&page=$counter\">$counter</a>&nbsp;";
		}
	} elseif ($lastpage > 5 + ($adjacents * 2)) { //enough pages to hide some
		if ($page_num_request < 1 + ($adjacents * 2)) { //close to beginning; only hide later pages
			for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
				if ($counter == $page_num_request) $pagination .= $page_num_request . '&nbsp;'; 
				else $pagination .= "<a href=\"$url&page=$counter\">$counter</a>&nbsp;";
			}
			$pagination .= "...&nbsp;";
			$pagination .= "<a href=\"$url&page=$lpm1\">$lpm1</a>&nbsp;";
			$pagination .= "<a href=\"$url&page=$lastpage\">$lastpage</a>&nbsp;";
		} elseif ($lastpage - ($adjacents * 2) > $page_num_request && $page_num_request > ($adjacents * 2)) { //in middle; hide some front and some back
			$pagination .= "<a href=\"$url&page=1\">1</a>&nbsp;";
			$pagination .= "<a href=\"$url&page=2\">2</a>&nbsp;";
			$pagination .= "...&nbsp;";
			for ($counter = $page_num_request - $adjacents; $counter <= $page_num_request + $adjacents; $counter++) {
				if ($counter == $page_num_request) $pagination .= $page_num_request . '&nbsp;'; 
				else $pagination .= "<a href=\"$url&page=$counter\">$counter</a>&nbsp;";
			}
			$pagination .= "...&nbsp;";
			$pagination .= "<a href=\"$url&page=$lpm1\">$lpm1</a>&nbsp;";
			$pagination .= "<a href=\"$url&page=$lastpage\">$lastpage</a>&nbsp;";
		} else { //close to end; only hide early pages
			$pagination .= "<a href=\"$url&page=1\">1</a>&nbsp;";
			$pagination .= "<a href=\"$url&page=2\">2</a>&nbsp;";
			$pagination .= "...&nbsp;";
			for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
				if ($counter == $page_num_request) $pagination .= $page_num_request . '&nbsp;'; 
				else $pagination .= "<a href=\"$url&page=$counter\">$counter</a>&nbsp;";
			}
		}
	}
	if ($page_num_request < $counter - 1) $pagination .= "<a href=\"$url&page=$next\">&#8594;</a>&nbsp;"; 
	else $pagination .= "&#8594;&nbsp;";
	$pagination .= "</div>";
}

if (isset($pagination)) {
	echo $pagination;
}

// String
if ($type == 'string') { ?>

<script>
function syntaxHighlight(json) {
	if (typeof json != 'string') {
		json = JSON.stringify(json, undefined, 4);
	}
	json = json.replace(/&/g, '&').replace(/</g, '<').replace(/>/g, '>');
	return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function(match) {
		var cls = 'number';
		if (/^"/.test(match)) {
			if (/:$/.test(match)) {
				cls = 'key';
			} else {
				cls = 'string';
			}
		} else if (/true|false/.test(match)) {
			cls = 'boolean';
		} else if (/null/.test(match)) {
			cls = 'null';
		}
		return '<span class="' + cls + '">' + match + '</span>';
	});
}

var jsonString = <?php echo $value; ?>;

</script>

<div>
	<a href="edit.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;type=string&amp;key=<?php echo urlencode($key)?>">
		<img src="images/edit.png" width="16" height="16" title="Edit" alt="[E]">
	</a>
	<span>&nbsp;</span> 
	<a href="delete.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;type=string&amp;key=<?php echo urlencode($key)?>" class="delval">
		<img src="images/delete.png" width="16" height="16" title="Delete" alt="[X]">
	</a>
</div>

<div class=data><pre id="jsonData" class=jsonstring><?php echo format_html($value)?></pre></div>
<script>$('#jsonData').html(syntaxHighlight(jsonString)); </script>

<?php }



// Hash
else if ($type == 'hash') { ?>

<table>
<tr><th><div>Key</div></th><th><div>Value</div></th><th><div>&nbsp;</div></th><th><div>&nbsp;</div></th></tr>

<?php foreach ($values as $hkey => $value) { ?>
	<tr <?php echo $alt ? 'class="alt"' : ''?>><td><div><?php echo format_html($hkey)?></div></td><td><div class=data><?php echo format_html($value)?></div></td><td><div>
		<a href="edit.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;type=hash&amp;key=<?php echo urlencode($key)?>&amp;hkey=<?php echo urlencode($hkey)?>"><img src="images/edit.png" width="16" height="16" title="Edit" alt="[E]"></a>
	</div></td><td><div>
		<a href="delete.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;type=hash&amp;key=<?php echo urlencode($key)?>&amp;hkey=<?php echo urlencode($hkey)?>" class="delval"><img src="images/delete.png" width="16" height="16" title="Delete" alt="[X]"></a>
	</div></td></tr>
<?php $alt = !$alt; } ?>

<?php }


// List
else if ($type == 'list') { ?>

<table>
<tr><th><div>Index</div></th><th><div>Value</div></th><th><div>&nbsp;</div></th><th><div>&nbsp;</div></th></tr>

<?php 
	if (($count_elements_page === false) && ($size > $count_elements_page)) {
		$start = 0;
		$end   = $size;
	} else {
		$start = $count_elements_page * ($page_num_request - 1);
		$end   = min($start + $count_elements_page, $size);
	}

	for ($i = $start; $i < $end; ++$i) {
		$value = $redis->lIndex($key, $i);
		$value = encodeOrDecode('load', $key, $value);
?>
	<tr <?php echo $alt ? 'class="alt"' : ''?>><td><div><?php echo $i?></div></td><td><div class=data><?php echo format_html($value)?></div></td><td><div>
		<a href="edit.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;type=list&amp;key=<?php echo urlencode($key)?>&amp;index=<?php echo $i?>"><img src="images/edit.png" width="16" height="16" title="Edit" alt="[E]"></a>
	</div></td><td><div>
		<a href="delete.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;type=list&amp;key=<?php echo urlencode($key)?>&amp;index=<?php echo $i?>" class="delval"><img src="images/delete.png" width="16" height="16" title="Delete" alt="[X]"></a>
	</div></td></tr>
<?php $alt = !$alt; } ?>

<?php }


// Set
else if ($type == 'set') {

?>
<table>
<tr><th><div>Value</div></th><th><div>&nbsp;</div></th><th><div>&nbsp;</div></th></tr>

<?php foreach ($values as $value) {
	$display_value = $redis->exists($value) ? '<a href="view.php?s='.$server['id'].'&d='.$server['db'].'&key='.urlencode($value).'">'.format_html($value).'</a>' : format_html($value);
?>
	<tr <?php echo $alt ? 'class="alt"' : ''?>><td><div class=data><?php echo $display_value ?></div></td><td><div>
		<a href="edit.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;type=set&amp;key=<?php echo urlencode($key)?>&amp;value=<?php echo urlencode($value)?>"><img src="images/edit.png" width="16" height="16" title="Edit" alt="[E]"></a>
	</div></td><td><div>
		<a href="delete.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;type=set&amp;key=<?php echo urlencode($key)?>&amp;value=<?php echo urlencode($value)?>" class="delval"><img src="images/delete.png" width="16" height="16" title="Delete" alt="[X]"></a>
	</div></td></tr>
<?php $alt = !$alt; } ?>

<?php }


// ZSet
else if ($type == 'zset') { ?>

<table>
<tr><th><div>Score</div></th><th><div>Value</div></th><th><div>&nbsp;</div></th><th><div>&nbsp;</div></th></tr>

<?php foreach ($values as $value) {
	$score         = $redis->zScore($key, $value);
	$display_value = $redis->exists($value) ? '<a href="view.php?s='.$server['id'].'&d='.$server['db'].'&key='.urlencode($value).'">'.format_html($value).'</a>' : format_html($value);
?>
	<tr <?php echo $alt ? 'class="alt"' : ''?>><td><div><?php echo $score?></div></td><td><div class=data><?php echo $display_value ?></div></td><td><div>
		<a href="edit.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;type=zset&amp;key=<?php echo urlencode($key)?>&amp;score=<?php echo $score?>&amp;value=<?php echo urlencode($value)?>"><img src="images/edit.png" width="16" height="16" title="Edit" alt="[E]"></a>
		<a href="delete.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;type=zset&amp;key=<?php echo urlencode($key)?>&amp;value=<?php echo urlencode($value)?>" class="delval"><img src="images/delete.png" width="16" height="16" title="Delete" alt="[X]"></a>
	</div></td></tr>
<?php $alt = !$alt; } ?>

<?php }

if ($type != 'string') { ?>
	</table>

	<p>
	<a href="edit.php?s=<?php echo $server['id']?>&amp;d=<?php echo $server['db']?>&amp;type=<?php echo $type?>&amp;key=<?php echo urlencode($key)?>" class="add">Add another value</a>
	</p>
<?php }

if (isset($pagination)) {
	echo $pagination;
}

require 'includes/footer.inc.php';
