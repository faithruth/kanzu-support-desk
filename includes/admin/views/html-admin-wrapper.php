<div id="admin-kanzu-support-desk">
	<h2 class="admin-ksd-title"><?php _e('Dashboard','kanzu-support-desk'); ?></h2>
	<div id="tabs" class="admin-ksd-container">
		<ul>
			<li><a href="#dashboard"><img src="<?php echo plugins_url( '/assets/images/icons/dashboard.png', KSD_PLUGIN_FILE ) ?>" /></a></li>
			<li><a href="#tickets"><img src="<?php echo plugins_url( '/assets/images/icons/tickets.png', KSD_PLUGIN_FILE ) ?>" /></a></li>
			<li><a href="#settings"><img src="<?php echo plugins_url( '/assets/images/icons/settings.png', KSD_PLUGIN_FILE ) ?>" /></a></li>
			<li><a href="#addons"><img src="<?php echo plugins_url( '/assets/images/icons/addons.png', KSD_PLUGIN_FILE ) ?>" /></a></li>
			<li><a href="#help"><img src="<?php echo plugins_url( '/assets/images/icons/help.png', KSD_PLUGIN_FILE ) ?>" /></a></li>
		</ul>
		<div id="dashboard" class="admin-ksd-content">
			<!--NB: The first line of each of the following included files is the title. We need this for localization-->
			<?php include_once('html-admin-dashboard.php'); ?>
		</div>
		<div id="tickets" class="admin-ksd-content">
			<?php include_once('html-admin-tickets.php'); ?>
		</div>
		<div id="settings" class="admin-ksd-content">
			<?php include_once('html-admin-settings.php'); ?>
		</div>
		<div id="addons" class="admin-ksd-content">
			<?php include_once('html-admin-addons.php'); ?>
		</div>
		<div id="help" class="admin-ksd-content">
			<?php include_once('html-admin-help.php'); ?>
		</div>
	</div>
</div>