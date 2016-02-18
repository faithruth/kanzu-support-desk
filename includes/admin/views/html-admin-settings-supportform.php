<div>
    <h3><?php _e ( 'Customer Support Form Options', 'kanzu-support-desk' ); ?> </h3>
    <div class="setting">
        <label for="show_support_tab"><?php _e ( 'Show support button', 'kanzu-support-desk' ); ?></label>
        <input name="show_support_tab"  type="checkbox" <?php checked ( $settings['show_support_tab'], "yes") ?> value="yes"  />
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( 'When enabled, shows a support button in the bottom left of your site which your customers can use to log new tickets', 'kanzu-support-desk' ); ?>"/>
    </div>
    <div class="setting show_support_tab">
        <label for="support_button_text"><?php _e ( 'Support button text', 'kanzu-support-desk' ); ?></label>
        <input type="text" value="<?php echo $settings['support_button_text']; ?>" size="15" name="support_button_text" />
    </div>
    <div class="setting">
        <label for="supportform_show_categories"><?php _e ( 'Show  categories', 'kanzu-support-desk' ); ?></label>
        <input name="supportform_show_categories"  type="checkbox" <?php checked ( $settings['supportform_show_categories'], "yes") ?> value="yes"  />
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( 'When enabled, a ticket category option is added to the support form.', 'kanzu-support-desk' ); ?>"/>
    </div>
    <div class="setting">
        <label for="supportform_show_severity"><?php _e ( 'Show severity options', 'kanzu-support-desk' ); ?></label>
        <input name="supportform_show_severity"  type="checkbox" <?php checked ( $settings['supportform_show_severity'], "yes") ?> value="yes"  />
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( 'When added a ticket severity option is added to the support form.', 'kanzu-support-desk' ); ?>"/>
    </div>
                <div class="setting">
                    <label for="enable_recaptcha"><?php _e ( 'Enable Google reCAPTCHA', 'kanzu-support-desk' ); ?></label>                
                    <input name="enable_recaptcha"  type="checkbox" <?php checked ( $settings['enable_recaptcha'], "yes") ?> value="yes"  />
                    <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( "Add Google reCAPTCHA to your site's forms to prevent spam", 'kanzu-support-desk' ); ?>"/>
                </div>
                <div class="setting enable_recaptcha">
                    <label for="recaptcha_site_key"><?php _e ( 'Google reCAPTCHA Site Key', 'kanzu-support-desk' ); ?></label>                
                    <input type="text" value="<?php echo $settings['recaptcha_site_key']; ?>" size="30" name="recaptcha_site_key" />
                    <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php printf ( __ ( 'Your Google reCAPTCHA Site Key. Get one at %s', 'kanzu-support-desk' ), 'https://www.google.com/recaptcha/admin' ); ?>"/>
                </div>
                <div class="setting enable_recaptcha">
                    <label for="recaptcha_secret_key"><?php _e ( 'Google reCAPTCHA Secret Key', 'kanzu-support-desk' ); ?></label>                
                    <input type="text" value="<?php echo $settings['recaptcha_secret_key']; ?>" size="30" name="recaptcha_secret_key" />
                    <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php printf ( __ ( 'Your Google reCAPTCHA Secret Key. Get one at %s', 'kanzu-support-desk' ), 'https://www.google.com/recaptcha/admin' ); ?>"/>
                </div>
                <div class="setting enable_recaptcha">
                    <label for="recaptcha_error_message"><?php _e ( 'Message on reCAPTCHA failure', 'kanzu-support-desk' ); ?></label>
                    <textarea cols="60" rows="4" name="recaptcha_error_message"><?php echo $settings['recaptcha_error_message']; ?></textarea>
                    <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( "Message to display in case Google reCAPTCHA continuously fails. This is very unlikely but just in case.", 'kanzu-support-desk' ); ?>"/>
                </div>    
</div>
