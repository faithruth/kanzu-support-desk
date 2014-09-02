<h2 class="admin-ksd-tab-title"><?php _e('Dashboard','kanzu-support-desk'); ?></h2>
<p><?php _e('Welcome to Kanzu Support Desk','kanzu-support-desk');  
 
	_e('Test');
	$DS=DIRECTORY_SEPARATOR;
	$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
	//include( $plugindir  . $DS . "unittests".$DS."models".$DS."Tickets.php");
	include( $plugindir  . $DS . "unittests".$DS."controllers".$DS."Tickets.php"); 
	
	
	


	?>

</p>



	
	
