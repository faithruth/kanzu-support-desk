<div id="ksd-gs-tabs">
  <ul>
	<li><a href="#ksd-gs-tabs-0"><?php esc_html_e( 'Welcome to KSD', 'kanzu-support-desk' ); ?></a></li>
	<li><a href="#ksd-gs-tabs-1"><?php esc_html_e( 'Tickets - The Basics', 'kanzu-support-desk' ); ?></a></li>
	<li><a href="#ksd-gs-tabs-2"><?php esc_html_e( 'Tickets - Management', 'kanzu-support-desk' ); ?></a></li>
	<li><a href="#ksd-gs-tabs-3"><?php esc_html_e( 'Tickets - Organization', 'kanzu-support-desk' ); ?></a></li>
	<li><a href="#ksd-gs-tabs-4"><?php esc_html_e( 'Customizing your set-up', 'kanzu-support-desk' ); ?></a></li>
	<li><a href="#ksd-gs-tabs-5"><?php esc_html_e( 'More Features', 'kanzu-support-desk' ); ?></a></li>
  </ul>
  <div id="ksd-gs-tabs-0">
	<h2><?php esc_html_e( 'Firing up your customer service', 'kanzu-support-desk' ); ?></h2>
	<p><?php esc_html_e( 'Kanzu Support Desk, or KSD, simplifies the process of offering amazing customer service to everyone who looks to you for it.', 'kanzu-support-desk' ); ?></p>
	<p><?php esc_html_e( "The plugin's built with your small business in mind; we know only too well how hard it is to manage multiple customer conversations while keeping all of them personal.", 'kanzu-support-desk' ); ?></p>
	<p><?php esc_html_e( "You get centralized management, ease of use, reports, multiple integrations and a responsive support team to look to in case you have any challenges. Let's start the tour, shall we?", 'kanzu-support-desk' ); ?></p>
	<a class="button button-primary ksd-gs-nav" href="#ksd-gs-tabs-1"><?php esc_html_e( 'Start Tour', 'kanzu-support-desk' ); ?></a>
	<div class="ksd-gs-kc-signature">
		<p>
		<?php esc_html_e( 'Your friends', 'kanzu-support-desk' ); ?>,<br/>
		<?php _ex( 'Team', 'company team e.g. WordPress team', 'kanzu-support-desk' ); ?> Kanzu Code</p>
		<img src="<?php echo KSD_PLUGIN_URL . '/assets/images/kanzu_code_logo.png'; ?>" />
	</div>
  </div>
  <div id="ksd-gs-tabs-1">
	<h2><?php esc_html_e( 'The basics...', 'kanzu-support-desk' ); ?></h2>
	<p><?php esc_html_e( "Let's take it from the top", 'kanzu-support-desk' ); ?></p>
	<p><strong><?php esc_html_e( 'What is a ticket?', 'kanzu-support-desk' ); ?></strong> <?php esc_html_e( 'Every conversation between you and your customer is called a ticket.', 'kanzu-support-desk' ); ?></p>
	<p><strong><?php esc_html_e( 'Who creates a ticket?', 'kanzu-support-desk' ); ?></strong> <?php esc_html_e( 'Usually, your customer creates a ticket by using a form on your website, sending an email to your support email address or getting in touch with you on social media. Also, you or one of your team can create a ticket on behalf of a customer.', 'kanzu-support-desk' ); ?></p>
			   <p><strong><?php esc_html_e( 'Who can view/manage/reply to a ticket?', 'kanzu-support-desk' ); ?></strong><?php esc_html_e( 'Management of tickets is restricted to certain WordPress roles. Tickets are not public. KSD comes with 3 custom roles: ', 'kanzu-support-desk' ); ?>
				<ul class="ksd-gs-user-roles">
					<li><strong><?php _ex( 'KSD Customer', 'WordPress role for customers', 'kanzu-support-desk' ); ?>:</strong><?php esc_html_e( 'This is the default role assigned to everyone who submits a ticket. It is the equivalent of the', 'kanzu-support-desk' ); ?> <a href="https://codex.wordpress.org/Roles_and_Capabilities#Subscriber" target="_blank"><?php esc_html_e( 'WordPress subscriber role', 'kanzu-support-desk' ); ?></a></li>
					<li><strong><?php _ex( 'KSD Agent', 'WordPress role for helpdesk staff', 'kanzu-support-desk' ); ?></strong><?php esc_html_e( 'This is a member of your team who can view, reply and make all changes to tickets apart from deleting them.', 'kanzu-support-desk' ); ?></li>
					<li><strong><?php _ex( 'KSD Supervisor', 'WordPress role for helpdesk staff', 'kanzu-support-desk' ); ?></strong> <?php esc_html_e( 'This role has all the rights of a KSD Agent but also, they can delete tickets', 'kanzu-support-desk' ); ?></li>
				</ul>
				<p><?php esc_html_e( "Assign the right role to the members of your team and they'll be able to easily manage tickets.", 'kanzu-support-desk' ); ?>
					<?php esc_html_e( 'Note that anyone with the WordPress role of administrator has unrestricted access to all functions. ', 'kanzu-support-desk' ); ?><a href="https://kanzucode.com/knowledge_base/help-desk-user-roles/" target="_blank" class="button button-primary"><?php esc_html_e( 'Read more on roles here', 'kanzu-support-desk' ); ?></a>
			   </p>
				<?php echo $nav_menu; ?>
  </div>
  <div id="ksd-gs-tabs-2">
	<h2><?php esc_html_e( 'Managing a ticket', 'kanzu-support-desk' ); ?></h2>
	<p><?php printf( '%s <strong>%s</strong> %s', __( 'All your tickets are listed under the', 'kanzu-support-desk' ), __( 'Tickets', 'kanzu-support-desk' ), __( 'menu. All tickets you have not yet read have a white background ', 'kanzu-support-desk' ) ); ?></p>
	<img src="<?php echo KSD_PLUGIN_URL . '/assets/images/ksd_tickets_read_unread.jpg'; ?>" class="ksd-gs-image" />
	<p><?php esc_html_e( 'Click on a ticket to manage it. This presents you a screen where you can change ticket status, ticket severity or who it is assigned to', 'kanzu-support-desk' ); ?></p>
	  <img src="<?php echo KSD_PLUGIN_URL . '/assets/images/ksd_ticket_info.jpg'; ?>" class="ksd-gs-image" />
	<p><?php esc_html_e( 'Reply to your customer or to staff only', 'kanzu-support-desk' ); ?></p>
	  <img src="<?php echo KSD_PLUGIN_URL . '/assets/images/ksd_reply_to_all.jpg'; ?>" class="ksd-gs-image" />
	<p><?php esc_html_e( 'View ticket activity and what other tickets have been logged by the customer', 'kanzu-support-desk' ); ?></p>
	<img src="<?php echo KSD_PLUGIN_URL . '/assets/images/ksd_other_tickets.jpg'; ?>" class="ksd-gs-image" />
	<?php echo $nav_menu; ?>
  </div>
  <div id="ksd-gs-tabs-3">
	<h2><?php esc_html_e( 'Organization is key', 'kanzu-support-desk' ); ?></h2>
	<p><?php esc_html_e( "For high efficiency, you'll need to organize your tickets. KSD allows two forms of categorization", 'kanzu-support-desk' ); ?>:</p>
	  <ol class="ksd-gs-categories">
		<li><strong><?php esc_html_e( 'Categories', 'kanzu-support-desk' ); ?>:</strong> <?php esc_html_e( 'Create multiple categories and subcategories to track tickets from particular clients (e.g. VIP), those from particular channels (e.g. email, facebook, website) or anything really.', 'kanzu-support-desk' ); ?></li>
		<li><strong><?php esc_html_e( 'Products', 'kanzu-support-desk' ); ?>:</strong> <?php esc_html_e( 'Create products to track tickets related to your products.', 'kanzu-support-desk' ); ?></li>
	  </ol>
			<img src="<?php echo KSD_PLUGIN_URL . '/assets/images/ksd_products.jpg'; ?>" class="ksd-gs-image" />
			<?php echo $nav_menu; ?>
  </div>
  <div id="ksd-gs-tabs-4">
	<h2><?php esc_html_e( 'Reports & Customization', 'kanzu-support-desk' ); ?></h2>
	<p><?php esc_html_e( 'Make changes to your set-up based on your needs. Set up replies to be sent automatically to your customer as soon as a ticket is created (auto-replies), select whom tickets should be automatically assigned to as soon as they are created and decide where your support page/form should be and what fields it should hold.', 'kanzu-support-desk' ); ?></p>
	<img src="<?php echo KSD_PLUGIN_URL . '/assets/images/ksd_settings.jpg'; ?>" class="ksd-gs-image" />
   <p><?php esc_html_e( 'Also, keep an eye on some key metrics', 'kanzu-support-desk' ); ?></p>
	<img src="<?php echo KSD_PLUGIN_URL . '/assets/images/ksd_dashboard.jpg'; ?>" class="ksd-gs-image" />
	<p><a class="button button-primary" href="https://kanzucode.com/knowledge_base/ksd-wordpress-helpdesk-plugin-settings/" target="_blank"> <?php esc_html_e( 'More on settings here', 'kanzu-support-desk' ); ?></a></p>
		<?php echo $nav_menu; ?>
  </div>
  <div id="ksd-gs-tabs-5">
	<h2><?php esc_html_e( 'Taking this further', 'kanzu-support-desk' ); ?>...</h2>
	<p><?php esc_html_e( 'It might seem that with all those features there surely cannot be more...but there are. Lots more actually.', 'kanzu-support-desk' ); ?></p>
	<p><?php esc_html_e( 'We have optional add-ons that allow you to', 'kanzu-support-desk' ); ?>:
	  <ul class="ksd-gs-addons">
		<li><strong><?php esc_html_e( 'Mail', 'kanzu-support-desk' ); ?>:</strong> <?php esc_html_e( 'Create tickets automatically from emails sent to your support email address', 'kanzu-support-desk' ); ?></li>
		<li><strong><?php esc_html_e( 'Chat', 'kanzu-support-desk' ); ?>:</strong> <?php esc_html_e( 'Set-up live chat and automatically create tickets from your conversations', 'kanzu-support-desk' ); ?></li>
		<li><strong><?php esc_html_e( 'Knowledge Base', 'kanzu-support-desk' ); ?>:</strong> <?php esc_html_e( 'Create a rich set of articles that your customers can refer to even without contacting you', 'kanzu-support-desk' ); ?></li>
		<li><strong><?php esc_html_e( 'Replies', 'kanzu-support-desk' ); ?>:</strong> <?php esc_html_e( 'Create message templates that you can use when replying a customer. ', 'kanzu-support-desk' ); ?></li>
	  </ul>
	  ...<?php esc_html_e( 'and a lot more', 'kanzu-support-desk' ); ?>....<a class="button button-seconday" href="<?php echo admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-addons' ); ?>" target="_blank"> <?php esc_html_e( 'More addons', 'kanzu-support-desk' ); ?></a>
	</p>
	<p><?php esc_html_e( "That's it!! In case you'd like to take this tour again, go to the admin menu at the top, click 'Quick Tour' and we'll show you around this neck of the woods again.", 'kanzu-support-desk' ); ?></p>
	<img src="<?php echo KSD_PLUGIN_URL . '/assets/images/ksd_admin_menu.jpg'; ?>" class="ksd-gs-image"/>
	<ul class="ksd-gs-navigation">
	  <li><a href="https://kanzucode.com/knowledge_base/simple-wordpress-helpdesk-plugin-quick-start/" target="_blank" class="button button-primary"><?php esc_html_e( 'Quick start guide', 'kanzu-support-desk' ); ?></a> </li>
	  <li><a class="button button-secondary" href="<?php echo admin_url( 'edit.php?post_type=ksd_ticket' ); ?>"><?php esc_html_e( 'Close Guide', 'kanzu-support-desk' ); ?></a></li>
	</ul>
  </div>
</div>
