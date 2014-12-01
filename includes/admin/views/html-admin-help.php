<h3><?php _e( "Initial Configuration","kanzu-support-desk" ); ?></h3>
<?php _e( "To set-up your installation and start an awesome ticketing experience, go to 'Kanzu Support Desk' in your side navigation and select 'Settings'","kanzu-support-desk" ); ?>
<h3><?php _e( "Documentation","kanzu-support-desk" ); ?></h3>
<?php _e( "Kindly go through readme.txt. If the instructions there aren't sufficient, please visit: <a href='http://kanzucode.com/kanzu-support-desk' target='_blank'>Kanzu Support Documentation</a>","kanzu-support-desk" ); ?>
<h3><?php _e( "Support","kanzu-support-desk" ); ?></h3>
<?php _e( "If you have any trouble, please get in touch with us: <a href='http://kanzucode.com/support' target='_blank'>Kanzu Support</a>","kanzu-support-desk" ); ?>
<h4><?php _e( "Show some love","kanzu-support-desk" ); ?></h4>
<?php _e( "Has Kanzu Support been helpful? If so, please give us a rating in the <a href='https://wordpress.org/plugins/kanzu-support-desk/' target='_blank'>WordPress plugin store</a>","kanzu-support-desk" ); ?>
<h4><?php _e( "Feedback","kanzu-support-desk" ); ?></h4>
<form action="#" id="ksd-feedback" method="POST">
    <?php _e( "We'd truly, truly love to hear from you. What's your experience with <strong>Kanzu Support Desk</strong>? What do you like? What do you love? What don't you like? What do you want us to fix or improve?","kanzu-support-desk" ); ?>    
    <textarea name="ksd_user_feedback" rows="5" cols="100"></textarea>
    <input name="action" type="hidden" value="ksd_reply_ticket" />
    <?php wp_nonce_field( 'ksd-send-feedback', 'feedback-nonce' ); ?>
    <input type="submit" class="button-secondary" name="ksd-feedback-submit" value="<?php _e('Holla','kanzu-support-desk'); ?>"/>
</form>
<?php do_action('ksd_display_help')?>