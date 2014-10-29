<div id="admin-kanzu-support-desk">
    <div class="admin-ksd-title">
        <h2><?php _e('Dashboard','kanzu-support-desk'); ?></h2>
        <!--@TODO Implement topnav as a filter and add content to it from the various tabs using filters-->
	<!--<ul class="top-nav wrap">
            <li class="back hidden"><a href="<?php echo admin_url('admin.php?page=ksd-tickets'); ?>" class="add-new-h2"><?php _e('Inbox','kanzu-support-desk'); ?></a></li>
            <li><a href="<?php echo admin_url('admin.php?page=ksd-new-ticket'); ?>" class="add-new-h2"><?php _e('New Ticket','kanzu-support-desk'); ?></a></li>
        </ul>-->
    </div>
	<div id="tabs" class="admin-ksd-container">
		<ul class="ksd-main-nav">
			<li><a href="#dashboard"><img src="<?php echo plugins_url( '/assets/images/icons/dashboard.png', KSD_PLUGIN_FILE ) ?>" title="<?php _e('Home','kanzu-support-desk'); ?>" /></a></li>
			<li><a href="#tickets"><img src="<?php echo plugins_url( '/assets/images/icons/tickets.png', KSD_PLUGIN_FILE ) ?>" title="<?php _e('Inbox','kanzu-support-desk'); ?>" /></a></li>
                        <li><a href="#new_ticket"><img src="<?php echo plugins_url( '/assets/images/icons/newticket.png', KSD_PLUGIN_FILE ) ?>" title="<?php _e('New Ticket','kanzu-support-desk'); ?>" /></a></li>
			<li><a href="#settings"><img src="<?php echo plugins_url( '/assets/images/icons/settings.png', KSD_PLUGIN_FILE ) ?>" title="<?php _e('Settings','kanzu-support-desk'); ?>"/></a></li>
			<li><a href="#add-ons"><img src="<?php echo plugins_url( '/assets/images/icons/addons.png', KSD_PLUGIN_FILE ) ?>" title="<?php _e('Add-ons','kanzu-support-desk'); ?>" /></a></li>
			<li><a href="#help"><img src="<?php echo plugins_url( '/assets/images/icons/help.png', KSD_PLUGIN_FILE ) ?>" title="<?php _e('Help','kanzu-support-desk'); ?>" /></a></li>
			<li><a href="#tests"><img src="<?php echo plugins_url( '/assets/images/icons/tests.png', KSD_PLUGIN_FILE ) ?>" title="<?php _e('Tests','kanzu-support-desk'); ?>"/></a></li>
		</ul>
		<div id="dashboard" class="admin-ksd-content">
			<!--NB: The first line of each of the following included files is the title. We need this for localization-->
			<?php include_once('html-admin-dashboard.php'); ?>
		</div>
		<div id="tickets" class="admin-ksd-content">
			<?php include_once('html-admin-tickets.php'); ?>
		</div>
                <div id="new_ticket" class="admin-ksd-content">
			<?php include_once('html-admin-new-ticket.php'); ?>
		</div>
		<div id="settings" class="admin-ksd-content">
			<?php include_once('html-admin-settings.php'); ?>
		</div>
		<div id="add-ons" class="admin-ksd-content">
			<?php include_once('html-admin-addons.php'); ?>
		</div>
		<div id="help" class="admin-ksd-content">
			<?php include_once('html-admin-help.php'); ?>
		</div>
		<div id="tests" class="admin-ksd-content">
			<?php include_once('html-admin-tests.php'); ?>
		</div>      
            <div class="ksd-dialog loading hidden">Loading...</div>
            <div class="ksd-dialog error hidden">Error</div>
            <div class="ksd-dialog success hidden">Success</div>
</div>