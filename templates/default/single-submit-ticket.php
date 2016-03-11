<?php 
$referer     = $_SERVER['HTTP_REFERER'];
$request_uri =  $_SERVER['REQUEST_URI'];

$settings = kanzu_support_desk::get_settings();
$onboarding_enabled = $settings['onboarding_enabled'];
if ( 'yes' === $onboarding_enabled ):
?>
<div class="ksd-onboarding-progress wp-core-ui">
<ol class="ksd-onboarding-stages">
				<li class="done"><?php  _e( 'Start tour', 'kanzu-support-desk' ); ?> </li>
				<li class="active"><?php  _e( 'Create ticket', 'kanzu-support-desk' ); ?> </li>
				<li class=""><?php  _e( 'Reply ticket', 'kanzu-support-desk' ); ?></li>
				<li class=""><?php  _e( 'Resolve ticket', 'kanzu-support-desk' ); ?></li>
				<li class=""><?php  _e( 'Assign ticket', 'kanzu-support-desk' ); ?></li>
				<li class=""><?php  _e( 'Ready!', 'kanzu-support-desk' ); ?></li>
</ol> 
    <a href="<?php echo admin_url('edit.php?post_type=ksd_ticket&ksd-onboarding=3')?>" class="button-small button button-primary ksd-mail-button">Next</a>
<div class="ksd-onboarding-notes"></div>
</div>
<?php
endif;
?>
<div class="ksd-new-ticket-form-wrap ksd-form-short-code">
    <?php $settings = Kanzu_Support_Desk::get_settings(); ?>
        <div class="ksd-close-form-wrapper">
            <img src="<?php echo KSD_PLUGIN_URL.'assets/images/icons/close.png'; ?>" class="ksd_close_button" width="32" height="32" Alt="<?php __('Close','kanzu-support-desk'); ?>" />
        </div>
        <form method="POST" class="ksd-new-ticket-public" enctype="multipart/form-data">
            <ul>      
            <?php if( "no" == $settings['enable_customer_signup'] && ! is_user_logged_in() ): ?>
                <li class="ksd-cust-fullname">       
                  <input type="text" value="<?php _e( 'Name', 'kanzu-support-desk' ); ?>" name="ksd_cust_fullname" label="Name" class="ksd-cust-fullname" minlength="2" required/>
                </li>
                <li class="ksd-customer-email">       
                  <input type="email" value="<?php _e( 'Email', 'kanzu-support-desk' ); ?>" name="ksd_cust_email" label="Email" class="ksd-customer-email" minlength="2" required/>
                </li>    
            <?php endif; ?>                
            <li class="ksd-subject">       
              <input type="text" value="<?php _e('Subject','kanzu-support-desk'); ?>" maxlength="255" name="ksd_tkt_subject" label="Subject" class="ksd-subject" minlength="2" required/>
            </li>
       <?php
            $show_categories    = $settings['supportform_show_categories'];
            $show_products      = $settings['supportform_show_products'];
            $show_severity      = $settings['supportform_show_severity'];  
            $show_attachment    = $settings['supportform_show_attachment'];   
            
            if( 'yes' === $show_severity ):
            ?>
            <li class="ksd-pdt-severity">  
                <select class="ksd-severity" name="ksd_tkt_severity" >
                    <option selected="selected" disabled="disabled"><?php _e( 'Severity' ); ?></option>
                    <option value="low"><?php echo _e( 'Low', 'kanzu-sipport-desk' ); ?></option>
                    <option value="medium"><?php echo _e( 'Medium', 'kanzu-sipport-desk' ); ?></option>
                    <option value="high"><?php echo _e( 'High', 'kanzu-sipport-desk' ); ?></option>
                    <option value="urgent"><?php echo _e( 'Urgent', 'kanzu-sipport-desk' ); ?></option>
                </select>
            </li>  
            <?php endif; ?>        
            <?php if( 'yes' === $show_products ): ?>
            <li class="ksd_pdt-categories" >
                <select  name="ksd_tkt_product_id" >
                    <option selected="selected" disabled="disabled"><?php _e( 'Product' ); ?></option>    
                    <?php
                        $products = get_categories( array( 'taxonomy' => 'product', 'hide_empty' => 0 ) );
                        foreach( $products  as $product ){
                            echo "<option value='{$product->term_id}'>{$product->name}</option>";
                        }
                    ?>
                </select>    
            </li>
            <?php endif; ?>     
            <?php if( 'yes' === $show_categories ): ?>
            <li class="ksd-tkt-categories" >
                <select  name="ksd_tkt_cat_id" >
                    <option selected="selected" disabled="disabled"><?php _e( 'Category' ); ?></option>    
                    <?php
                        $cats = get_categories( array(  'taxonomy' => 'ticket_category', 'hide_empty' => 0 ) );
                        foreach( $cats  as $cat ){
                            echo "<option value='{$cat->term_id}'>{$cat->name}</option>";
                        }
                    ?>
                </select>    
            </li>
            <?php endif; ?>    
            
            <?php if( 'yes' == $show_attachment ): ?>
            <li class="ksd-tkt-attachment" >
                <input type="file" name="ksd_tkt_attachment"/>
            </li>
            <?php endif; ?> 
            
              <li class="ksd-message">     
                  <textarea value="<?php _e('Message','kanzu-support-desk'); ?>" rows="7" class="ksd-message" name="ksd_tkt_message" required></textarea>
              </li>
            <!--Add Google reCAPTCHA-->
            <?php if( "yes" == $settings['enable_recaptcha'] && $settings['recaptcha_site_key'] !== '' ): ?>
                <li class="ksd-g-recaptcha">
                    <span class="ksd-g-recaptcha-error"></span>
                    <div class="g-recaptcha" data-sitekey="<?php echo $settings['recaptcha_site_key']; ?>"></div>
                </li>
            <?php endif; ?>
              <li class="ksd-public-submit">
                <img src="<?php echo KSD_PLUGIN_URL.'assets/images/loading_dialog.gif'; ?>" class="ksd_loading_dialog" width="45" height="35" />
                <input type="submit" value="<?php _e( 'Send Message', 'kanzu-support-desk'); ?>" name="ksd-submit-tab-new-ticket" class="ksd-submit"/>
              </li>
            </ul>
            <input name="action" type="hidden" value="ksd_log_new_supportform_ticket" />
            <input name="ksd_tkt_channel" type="hidden" value="support-tab" />
            <?php if( "yes" == $settings['onboarding_enabled'] ):?>
                <input name="onboarding_enabled" type="hidden" value="onboarding_enabled" /><!--@TODO In log_new_ticket,after logging, if this is present, 
                                                                                            do_action('after_onboarding_ticket',array($this, method_name),$new_ticket_id); method_name, for now, just redirects
                                                                                            to the reply ticket screen of the new ticket but when that screen's being created,if( "yes" == $settings['onboarding_enabled'] ):,
                                                                                            an extra field is added. On that screen, introjs can highlight 'Resolve ticket' and 'Assign ticket' before prompting the user  to reply. After they
                                                                                            reply, if extra field is present, redirect to settings page & introjs explains 'enable autoreply','autoassign' and 'support forms'. Tour is done. Thanks. Message explains how to re-enable the tour
                                                                                            and how to send feedback/ask for help-->
            <?php endif; ?>
            <?php wp_nonce_field( 'ksd-new-ticket', 'new-ticket-nonce' ); ?>
        </form>
        <div class="ksd-form-short-code-form-response ksd-support-form-response"></div>
    </div>