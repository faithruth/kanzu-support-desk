<h2 class="admin-ksd-tab-title"><?php _e('Dashboard','kanzu-support-desk'); ?></h2>
<p><?php _e('Welcome to Kanzu Support Desk','kanzu-support-desk');  
 
	_e('Test');
	$DS=DIRECTORY_SEPARATOR;
	$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
	//include_once( $plugindir  . $DS . "unittests".$DS."models".$DS."Tickets.php");
	include_once( KANZU_PLUGIN_ADMIN_DIR . KANZU_DS ."controllers". KANZU_DS ."Tickets.php"); 
	include_once( KANZU_PLUGIN_ADMIN_DIR . KANZU_DS ."controllers". KANZU_DS ."Users.php");
	


	?>

</p>



	
	
