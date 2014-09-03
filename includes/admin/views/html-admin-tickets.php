<h2 class="admin-ksd-tab-title"><?php _e('Tickets','kanzu-support-desk'); ?></h2>
	<div id="ticket-tabs" class="admin-ksd-tickets-container">
		<ul>
			<li><a href="#tickets-tab-1"><?php _e('My unresolved tickets','kanzu-support-desk'); ?></a></li>
			<li><a href="#tickets-tab-2"><?php _e('All tickets','kanzu-support-desk'); ?></a></li>
			<li><a href="#tickets-tab-3"><?php _e('Unassigned tickets','kanzu-support-desk'); ?></a></li>
			<li><a href="#tickets-tab-4"><?php _e('Recently updated','kanzu-support-desk'); ?></a></li>
			<li><a href="#tickets-tab-5"><?php _e('Recently resolved','kanzu-support-desk'); ?></a></li>
			<li><a href="#tickets-tab-6"><?php _e('Closed','kanzu-support-desk'); ?></a></li>
		</ul>
		<div id="tickets-tab-1" class="admin-ksd-tickets-content pending">
			<?php include_once('html-admin-tickets-list.php'); ?>
		</div>
		<div id="tickets-tab-2" class="admin-ksd-tickets-content pending">
			<p>Loading...</p>
		</div>
		<div id="tickets-tab-3" class="admin-ksd-tickets-content pending">
			<p>Loading..</p>
		</div>
		<div id="tickets-tab-4" class="admin-ksd-tickets-content pending">
			<p>Loading..</p>
		</div>
		<div id="tickets-tab-5" class="admin-ksd-tickets-content pending">
			<p>Loading..</p>
		</div>
		<div id="tickets-tab-6" class="admin-ksd-tickets-content pending">
			<p>Loading..</p>
		</div>
	</div>