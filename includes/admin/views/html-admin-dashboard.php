<div id="kanzu-dashboard">
	<?php _e('Welcome to Kanzu Support Desk','kanzu-support-desk'); ?>
	
	
	
	<?php
	_e('Test');
	$DS=DIRECTORY_SEPARATOR;
	$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
	include( $plugindir  . $DS . "unittests".$DS."models".$DS."Tickets.php");


	?>


</div>