<div id="admin-kanzu-support-desk">
	<div id="tabs" class="admin-ksd-container">
		<ul>
			<li><a href="#dashboard"><img src="<?php echo plugins_url( '/assets/images/icons/dashboard.png', KSD_PLUGIN_FILE ) ?>" title="Dashboard" /></a></li>
			<li><a href="#tickets"><img src="<?php echo plugins_url( '/assets/images/icons/tickets.png', KSD_PLUGIN_FILE ) ?>" title="Tickets" /></a></li>
			<li><a href="#settings"><img src="<?php echo plugins_url( '/assets/images/icons/settings.png', KSD_PLUGIN_FILE ) ?>" title="Settings" /></a></li>
			<li><a href="#addons"><img src="<?php echo plugins_url( '/assets/images/icons/addons.png', KSD_PLUGIN_FILE ) ?>" title="Addons"/></a></li>
			<li><a href="#help"><img src="<?php echo plugins_url( '/assets/images/icons/help.png', KSD_PLUGIN_FILE ) ?>"  title="Help"/></a></li>
		</ul>
		<div id="dashboard" class="admin-ksd-content">
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
		<div id="help" class="admin-ksd-content" >
			<?php include_once('html-admin-help.php'); ?>
		</div>
	</div>
</div>