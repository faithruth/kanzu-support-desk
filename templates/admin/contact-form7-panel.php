<h2><?php _e( 'Kanzu Support Desk', 'kanzu-support-desk' ); ?></h2>
<label for="_ksd_wpcf7_enabled"><?php _e( 'Enable ticket creation', 'kanzu-support-desk' ); ?></label>
<input type="checkbox" name="_ksd_wpcf7_enabled" <?php checked( get_post_meta( $cf7_form->id, '_ksd_wpcf7_enabled', true ), 'yes' );?> value="yes"/>
<p>
	<?php _e( 'If enabled, all messages sent using this form will be converted into tickets.','kanzu-support-desk' );
?>
</p>
<div class="ksd-wpcf7-config-wrapper">
	<p>
		<?php _e( "Use the contact form tags below to specify which fields to use for the support ticket name, email, subject and message. We auto-populate these fields for you based on the form's default tags.",'kanzu-support-desk' );
	?>
	</p>
	<p><strong><?php _e( "Tags to use: ",'kanzu-support-desk' ); ?></strong><?php echo $cf7_suggested_mail_tags; ?></p>
	<form class="ksd-contact-form-7">
	        <div class="ksd-cf7-field ksd-cust-fullname-cf7">
	          <label for="_ksd_wpcf7_name"><?php _e( 'Name', 'kanzu-support-desk' ); ?></label>
	          <input type="text" name="_ksd_wpcf7_name" class="ksd-cust-fullname large-text code" value="<?php echo get_post_meta( $cf7_form->id, '_ksd_wpcf7_name', true );?>"/>
	        </div>
	        <div class="ksd-cf7-field ksd-customer-email-cf7">
	        	<label for="_ksd_wpcf7_email"><?php _e( 'Email', 'kanzu-support-desk' ); ?></label>
	          	<input type="text" name="_ksd_wpcf7_email" class="ksd-customer-email large-text code" value="<?php echo get_post_meta( $cf7_form->id, '_ksd_wpcf7_email', true );?>"/>
	        </div>
	    	<div class="ksd-cf7-field ksd-subject-cf7">
	    		<label for="_ksd_wpcf7_subject"><?php _e( 'Subject', 'kanzu-support-desk' ); ?></label>
	      		<input type="text" name="_ksd_wpcf7_subject" class="ksd-subject large-text code" value="<?php echo get_post_meta( $cf7_form->id, '_ksd_wpcf7_subject', true );?>" />
	    	</div>
	    	<div class="ksd-cf7-field ksd-message-cf7">
	    		<label for="_ksd_wpcf7_message"><?php _e( 'Message', 'kanzu-support-desk' ); ?></label>
	      		<input type="text" name="_ksd_wpcf7_message" class="ksd-message large-text code" value="<?php echo get_post_meta( $cf7_form->id, '_ksd_wpcf7_message', true );?>"/>
	    	</div>
	</form>
	<p>
		<?php _e( 'To test, send a message using the form and confirm that a ticket is created', 'kanzu-support-desk' ); ?>
	</p>
	<a href="https://kanzucode.com/knowledge_base/ksd-contact-form-7-integration-quickstart/" target="_blank"><?php _e( 'Configuration Help', 'kanzu-support-desk'); ?></a>
</div>
