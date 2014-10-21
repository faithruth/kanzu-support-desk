<h2 class="admin-ksd-tab-title"><?php __('Settings','kanzu-support-desk'); ?></h2>
Customize your Kanzu Support Desk experience
<form method="POST" id="update-settings" class="ksd-settings pending">     
    <div class="setting">
        <label for="mail_server">Mail Server</label>
        <input type="text" value="<?php _e('mail.example.com','kanzu-support-desk'); ?>" size="30" name="mail_server" />
    </div>
    <div class="setting">
        <label for="mail_account">Support Email Address</label>
        <input type="text" value="<?php _e('user@example.com','kanzu-support-desk'); ?>" size="30" name="mail_account" />
    </div>
    <div class="setting">
        <label for="mail_password">Password</label>
        <input type="password"  size="30" name="mail_password" />
    </div>
    <div class="setting">
        <label for="mail_protocol">Protocol</label>
        <select name="mail_protocol">
            <option value="pop3" <?php selected( "pop3", $settings['mail_protocol'] ) ?>>POP3</option>
            <option value="imap"  <?php selected( "imap", $settings['mail_protocol'] ) ?> >IMAP</option>
        </select>
    </div> 
    <div class="setting">
        <label for="mail_useSSL">Use SSL</label>
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('To use this, your server must have a valid SSL certificate. Otherwise, disable this option','kanzu-support-desk')  ;?>"/>
        <input name="mail_useSSL"  type="checkbox" <?php checked( $settings['mail_useSSL'], "yes" ) ?> value="yes"  />
    </div>
    <div class="setting">
        <label for="mail_validate_certificate">Validate Certificate</label>
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('Validate your SSL certificate during connection. Use only if you have a valid SSL certificate otherwise it will fail','kanzu-support-desk')  ;?>"/>
        <select name="mail_validate_certificate">
            <option value="no" <?php selected( "no", $settings['mail_validate_certificate'] ) ?>>NO</option>
            <option value="yes" <?php selected( "yes", $settings['mail_validate_certificate'] ) ?>>YES</option>            
        </select>
    </div> 
    <input name="action" type="hidden" value="ksd_update_settings" />    
    <?php wp_nonce_field( 'ksd-update-settings', 'update-settings-nonce' ); ?>
    <input type="submit" value="<?php _e( "Update","kanzu-support-desk" ); ?>" name="ksd-settings-submit" class="ksd-submit button button-primary button-large"/>
 </form>
<div class="success hidden"></div>
<div class="loading hidden"></div>
