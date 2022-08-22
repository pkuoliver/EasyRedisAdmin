<?php
require_once 'includes/common.inc.php';

$page['css'][] = 'frame';
$page['js'][]  = 'frame';

require 'includes/header.inc.php';

?>
<h2>Redis Info :</h2>
<script src="js/json.js"></script>
<?php
	$value = json_encode($redis->info(), JSON_PRETTY_PRINT);
?>
<script>
var jsonString = <?php echo $value; ?>;
</script>

<div class=data><pre id="jsonData" class=jsonstring><?php echo format_html($value)?></pre></div>
<script>$('#jsonData').html(syntaxHighlight(jsonString)); </script>

<?php

require 'includes/footer.inc.php';
?>