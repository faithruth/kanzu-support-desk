<div>
    <h3><?php _e ( 'Customer Support Form Options', 'kanzu-support-desk' ); ?> </h3>  
    <div class="setting">
        <label for="page_submit_ticket"><?php _e ( 'Support Form Page', 'kanzu-support-desk' ); ?></label>            
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php  _e( 'This is the page on which customers will submit tickets. The [ksd_support_form] short code must be on this page.', 'kanzu-support-desk' ); ?>"/>
        <select name="page_submit_ticket">
        <option value="0">--</option>
        <?php foreach ( get_pages ( ) as $page ) { ?>
            <option value="<?php echo $page->ID; ?>" 
                <?php selected ( $page->ID, $settings['page_submit_ticket']); ?>> 
                    <?php echo $page->post_title; ?>  
            </option>
        <?php } ?>                         
        </select> 
    </div>     
    <div class="setting">
        <label for="show_support_tab"><?php _e ( 'Show support button', 'kanzu-support-desk' ); ?></label>
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php  _e( 'When enabled, shows a support button in the bottom right of your site which your customers can use to log new tickets', 'kanzu-support-desk' ); ?>"/>
        <input name="show_support_tab"  type="checkbox" <?php checked ( $settings['show_support_tab'], "yes") ?> value="yes"  />        
    </div>
    
    <div class="setting show_support_tab">
        <label for="support_button_text"><?php _e ( 'Support button text', 'kanzu-support-desk' ); ?></label>
        <input type="text" value="<?php echo $settings['support_button_text']; ?>" size="15" name="support_button_text" />
    </div>
    <div class="setting">
        <label for="page_my_tickets"><?php _e ( 'My Tickets Page', 'kanzu-support-desk' ); ?></label>
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php  _e( 'This is the page on which customers will view all their tickets. The [ksd_my_tickets] short code must be on this page.', 'kanzu-support-desk' ); ?>"/>
        <select name="page_my_tickets">
            <option value="0">--</option>
            <?php foreach ( get_pages ( ) as $page ) { ?>
                <option value="<?php echo $page->ID; ?>" 
                    <?php selected ( $page->ID, $settings['page_my_tickets']); ?>> 
                    <?php echo $page->post_title; ?>  
                </option>
                    <?php } ?>                         
        </select>
    </div>     
    <h3><?php _e ( 'Support Form fields', 'kanzu-support-desk' ); ?></h3>
    <div class="setting">
        <label for="enable_customer_signup"><?php _e ( 'Enable customer registration', 'kanzu-support-desk' ); ?></label>
        <div class="ksd-input-wrapper">
            <input name="enable_customer_signup"  type="checkbox" <?php checked ( $settings['enable_customer_signup'], "yes") ?> value="yes"  />
            <span class="description"><?php _e( 'Customer must be registered to create a ticket. If disabled, an account will be automatically created for the customer on ticket creation and a unique hash URL used for the ticket.','kanzu-support-desk' ); ?></span>
        </div>
    </div>      
    <div class="setting">
        <label for="supportform_show_categories"><?php _e ( 'Show  categories', 'kanzu-support-desk' ); ?></label>
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php  _e( 'When enabled, a ticket category option is added to the support form.', 'kanzu-support-desk' ); ?>"/>
        <input name="supportform_show_categories"  type="checkbox" <?php checked ( $settings['supportform_show_categories'], "yes") ?> value="yes"  />        
    </div>
    <div class="setting">
        <label for="supportform_show_products"><?php _e ( 'Show  Products', 'kanzu-support-desk' ); ?></label>
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php  _e( 'When enabled, your products are added to the support form.', 'kanzu-support-desk' ); ?>"/>
        <input name="supportform_show_products"  type="checkbox" <?php checked ( $settings['supportform_show_products'], "yes") ?> value="yes"  />        
    </div>    
    <div class="setting">
        <label for="supportform_show_severity"><?php _e ( 'Show severity options', 'kanzu-support-desk' ); ?></label>        
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php  _e( 'When enabled, a ticket severity option is added to the support form.', 'kanzu-support-desk' ); ?>"/>
        <input name="supportform_show_severity"  type="checkbox" <?php checked ( $settings['supportform_show_severity'], "yes") ?> value="yes"  />        
    </div>
    <div class="setting">
        <label for="enable_recaptcha"><?php _e ( 'Enable Google reCAPTCHA', 'kanzu-support-desk' ); ?></label>                        
        <div class="ksd-input-wrapper">
            <input name="enable_recaptcha"  type="checkbox" <?php checked ( $settings['enable_recaptcha'], "yes") ?> value="yes"  />        
            <span class="description"><?php _e ( "Add Google reCAPTCHA to your site's forms to prevent spam", 'kanzu-support-desk' ); ?></span>
        </div>
    </div>
    
    <div class="setting enable_recaptcha">
        <label for="recaptcha_site_key"><?php _e ( 'Google reCAPTCHA Site Key', 'kanzu-support-desk' ); ?></label>                
        <input type="text" value="<?php echo $settings['recaptcha_site_key']; ?>" size="45" name="recaptcha_site_key" />        
        <span class="description"><?php printf ( __ ( 'Your Google reCAPTCHA Site Key. Get one at %s', 'kanzu-support-desk' ), '<a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA</a>' ); ?></span>
    </div>
    <div class="setting enable_recaptcha">
        <label for="recaptcha_secret_key"><?php _e ( 'Google reCAPTCHA Secret Key', 'kanzu-support-desk' ); ?></label>                
        <input type="text" value="<?php echo '************************************'.substr( $settings['recaptcha_secret_key'], -4 ); ?>" size="45" name="recaptcha_secret_key" />      
        <span class="description"><?php printf ( __ ( 'Your Google reCAPTCHA Secret Key. Get one at %s', 'kanzu-support-desk' ), '<a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA</a>' ); ?></span>
    </div>
    <div class="setting enable_recaptcha">
        <label for="recaptcha_error_message"><?php _e ( 'Message on reCAPTCHA failure', 'kanzu-support-desk' ); ?></label>
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( "Message to display in case Google reCAPTCHA continuously fails. This is very unlikely but just in case.", 'kanzu-support-desk' ); ?>"/>
        <textarea cols="60" rows="4" name="recaptcha_error_message"><?php echo $settings['recaptcha_error_message']; ?></textarea>        
    </div>    
</div>