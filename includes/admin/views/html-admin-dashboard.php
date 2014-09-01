<div id="kanzu-dashboard">
	<?php _e('Welcome to Kanzu Support Desk','kanzu-support-desk'); ?>
	



</div>

<h2 class="admin-ksd-tab-title"><?php _e('Dashboard','kanzu-support-desk'); ?></h2>
<p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. Nam elementum quam ullamcorper ante. Etiam aliquet massa et lorem. Mauris dapibus lacus auctor risus. Aenean tempor ullamcorper leo. Vivamus sed magna quis ligula eleifend adipiscing. Duis orci. Aliquam sodales tortor vitae ipsum. Aliquam nulla. Duis aliquam molestie erat. Ut et mauris vel pede varius sollicitudin. Sed ut dolor nec orci tincidunt interdum. Phasellus ipsum. Nunc tristique tempus lectus.</p>



	
	
	<?php
	_e('Test');
	$DS=DIRECTORY_SEPARATOR;
	$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
	include( $plugindir  . $DS . "unittests".$DS."models".$DS."Tickets.php");


	?>