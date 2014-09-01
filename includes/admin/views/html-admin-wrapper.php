<div id="admin-kanzu-support-desk">
	<div id="tabs" class="admin-ksd-container">
		<ul>
			<li><a href="#dashboard"><?php _e('Dashboard','kanzu-support-desk'); ?></a></li>
			<li><a href="#tickets"><?php _e('Tickets','kanzu-support-desk'); ?></a></li>
			<li><a href="#settings"><?php _e('Settings','kanzu-support-desk'); ?></a></li>
			<li><a href="#addons"><?php _e('Add-ons','kanzu-support-desk'); ?></a></li>
			<li><a href="#help"><?php _e('Help','kanzu-support-desk'); ?></a></li>
		</ul>
	<div id="dashboard">
		<?php include_once('html-admin-dashboard.php'); ?>
	</div>
	<div id="tickets">
		<?php include_once('html-admin-tickets.php'); ?>
	</div>
	<div id="settings">
		<?php include_once('html-admin-settings.php'); ?>
	</div>
	<div id="addons">
		<?php include_once('html-admin-addons.php'); ?>
	</div>
	<div id="help">
		<?php include_once('html-admin-help.php'); ?>
	</div>
	</div>
</div>