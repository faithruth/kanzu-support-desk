<h3><?php _e( 'Kanzu Support Desk: Need help? Got feedback?', 'kanzu-support-desk' );  ?></h3>
<p><?php _e( 'We would love to hear from you. Send us feedback on what you love, what features you would like or simply get in touch for help.', 'kanzu-support-desk' );  ?></p>
<form id="ksd_support_tab_form" class="ksd-feedback-support-tab">
	<div>
		<input type="text" name="ksd_support_tab_subject" placeholder="<?php _e( 'Subject', 'kanzu-support-desk' );  ?>" />
	</div>
	<div>
		<textarea name="ksd_support_tab_message" placeholder="<?php _e( 'Your Message', 'kanzu-support-desk' );  ?>" rows="5" cols="100"></textarea>
	</div>
	<input name="action" type="hidden" value="ksd_support_tab_send_feedback" />
	<div class="ksd-dialog loading hidden"><?php __( 'Loading...', 'kanzu-support-desk'); ?></div>
	<input type="submit" name="ksd_support_tab_submit" class="button button-primary" value="<?php _e( 'Send', 'kanzu-support-desk' );  ?>" />
</form>
<div class="ksd-feedback-response"></div>