<form method="POST" id="update-settings" class="ksd-settings pending"> 
    <div id="settings-accordion">
        <?php $settings = Kanzu_Support_Desk::get_settings();?>
         <h3><?php _e("Tickets","kanzu-support-desk"); ?></h3>
         <div>
             <div class="setting">
                <label for="enable_new_tkt_notifxns">Enable new ticket notifications</label>
                <input name="enable_new_tkt_notifxns"  type="checkbox" <?php checked( $settings['enable_new_tkt_notifxns'], "yes" ) ?> value="yes"  />
             </div>
             <div class="enable_new_tkt_notifxns">
                <div class="setting">
                   <label for="ticket_mail_from_name">From (Name)</label>
                   <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e("Defaults to the primary administrator's display name",'kanzu-support-desk')  ;?>"/>
                   <input type="text" value="<?php echo $settings['ticket_mail_from_name']; ?>" size="30" name="ticket_mail_from_name" />
               </div>
               <div class="setting">
                   <label for="ticket_mail_from_email">From (Email Address)</label>
                   <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e("Defaults to the primary administrator's email address",'kanzu-support-desk')  ;?>"/>
                   <input type="text" value="<?php echo $settings['ticket_mail_from_email']; ?>" size="30" name="ticket_mail_from_email" />
               </div>
               <div class="setting">
                   <label for="ticket_mail_subject">Subject</label>
                   <input type="text" value="<?php echo $settings['ticket_mail_subject']; ?>" size="60" name="ticket_mail_subject" />
               </div>
               <div class="setting">
                   <label for="ticket_mail_message">Message</label>
                   <textarea cols="60" rows="4" name="ticket_mail_message"><?php echo $settings['ticket_mail_message']; ?></textarea>
               </div>
             </div><!--.enable_new_tkt_notifxns-->
            <div class="setting">
                <label for="recency_definition">Recency Definition ( In Hours ) </label>
                <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e("In the ticket view, the 'Recently Updated' & 'Recently resolved' tabs, show tickets updated in last X hours",'kanzu-support-desk')  ;?>"/>
                <input type="text" value="<?php echo $settings['recency_definition']; ?>" size="15" name="recency_definition" />
            </div>
            <div class="setting">
                <label for="show_support_tab">Show support tab</label>
                <input name="show_support_tab"  type="checkbox" <?php checked( $settings['show_support_tab'], "yes" ) ?> value="yes"  />
             </div>
             <div class="setting">
                   <label for="tab_message_on_submit">Tab message on ticket submission</label>
                   <textarea cols="60" rows="4" name="tab_message_on_submit"><?php echo $settings['tab_message_on_submit']; ?></textarea>
             </div>
         </div>
         <h3><?php _e("Mail","kanzu-support-desk"); ?></h3>
         <div>
            <div class="setting">
                <label for="mail_server">Mail Server</label>
                <input type="text" value="<?php echo $settings['mail_server']; ?>" size="30" name="mail_server" />
            </div>
            <div class="setting">
                <label for="mail_account">Support Email Address</label>
                <input type="text" value="<?php echo $settings['mail_account']; ?>" size="30" name="mail_account" />
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
                <label for="mail_port">Port</label>
                        <input type="text" value="<?php echo $settings['mail_port']; ?>" size="30" name="mail_port" />
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
         </div>
    </div>
    <input name="action" type="hidden" value="ksd_update_settings" />    
    <?php wp_nonce_field( 'ksd-update-settings', 'update-settings-nonce' ); ?>
    <input type="submit" value="<?php _e( "Update","kanzu-support-desk" ); ?>" name="ksd-settings-submit" class="ksd-submit button button-primary button-large"/>
    <input type="submit" value="<?php _e( "Reset Defaults","kanzu-support-desk" ); ?>" name="ksd-settings-reset" class="ksd-submit ksd-reset button action button-large"/>
 </form>
