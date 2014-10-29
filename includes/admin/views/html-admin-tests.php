<div class="ksd-grid-container outline">
   <div class="ksd-row">
        <div class="ksd-col-1"><p>col-1</p></div> 
        <div class="ksd-col-1"><p>col-1</p></div> 
        <div class="ksd-col-1"><p>col-1</p></div> 
        <div class="ksd-col-1"><p>col-1</p></div> 
        <div class="ksd-col-1"><p>col-1</p></div> 
        <div class="ksd-col-1"><p>col-1 </p></div> 
    </div> 
   
    <div class="ksd-row">
        <div class="ksd-col-2"><p>col-2</p></div> 
        <div class="ksd-col-2"><p>col-2</p></div> 
        <div class="ksd-col-2"><p>col-2</p></div> 
    </div> 
    
    <div class="ksd-row">
        <div class="ksd-col-3"><p>col-3</p></div> 
        <div class="ksd-col-3"><p>col-3</p></div> 
    </div> 
</div>


<p><?php _e('Kanzu Support Desk Test Suite','kanzu-support-desk');  
 
	 
	$DS=DIRECTORY_SEPARATOR;
	$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
	include_once( $plugindir  . $DS . "unittests".$DS."models".$DS."Tickets.php");
	//include_once( KANZU_PLUGIN_ADMIN_DIR . KANZU_DS ."controllers". KANZU_DS ."Tickets.php"); 
	//include_once( KANZU_PLUGIN_ADMIN_DIR . KANZU_DS ."controllers". KANZU_DS ."Users.php");


	?>

</p>



	
	
