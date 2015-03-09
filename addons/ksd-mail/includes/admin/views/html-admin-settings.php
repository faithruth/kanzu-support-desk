<?php 
$mail_settings = $current_settings[KSD_MAIL_OPTIONS_KEY];
?>
<h3><?php _e("Mail","kanzu-support-desk"); ?> </h3>
                <div>
                   <div class="setting">
                       <label for="ksd_mail_server"><?php _e("Mail Server","kanzu-support-desk"); ?></label>
                       <input type="text" value="<?php echo $mail_settings['ksd_mail_server']; ?>" size="30" name="ksd_mail_server" />
                   </div>
                   <div class="setting">
                       <label for="ksd_mail_account"><?php _e("Support Email Address","kanzu-support-desk"); ?></label>
                       <input type="text" value="<?php echo $mail_settings['ksd_mail_account']; ?>" size="30" name="ksd_mail_account" />
                   </div>
                   <div class="setting">
                       <label for="ksd_mail_password"><?php _e("Password","kanzu-support-desk"); ?></label>
                       <input type="password"  size="30" name="ksd_mail_password" value="<?php echo $mail_settings['ksd_mail_password']; ?>"/>
                   </div>
                   <div class="setting">
                       <label for="ksd_mail_protocol"><?php _e("Protocol","kanzu-support-desk"); ?></label>
                       <select name="ksd_mail_protocol">
                           <option value="pop3" <?php selected( "pop3", $mail_settings['ksd_mail_protocol'] ); ?>>POP3</option>
                           <option value="imap"  <?php selected( "imap", $mail_settings['ksd_mail_protocol'] ); ?> >IMAP</option>
                       </select>
                   </div> 
                       <div class="setting">
                       <label for="ksd_mail_port"><?php _e("Port","kanzu-support-desk"); ?></label>                       
                       <input type="text" value="<?php echo $mail_settings['ksd_mail_port']; ?>" size="30" name="ksd_mail_port" />
                       <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php printf( __('Default Ports are: %1$d (%2$s), %3$d (%4$s), %5$d (%6$s) and %7$d (%8$s)','kanzu-support-desk'), 143,'IMAP',993,'IMAP/SSL',110,'POP3',995,'POP3/SSL' )  ;?>"/>
                   </div> 
                   <div class="setting">
                       <label for="ksd_mail_mailbox"><?php _e("Mailbox","kanzu-support-desk"); ?></label>                       
                       <input type="text" value="<?php echo $mail_settings['ksd_mail_mailbox']; ?>" size="30" name="ksd_mail_mailbox" />
                       <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('The mailbox to query. You almost never need to change this','kanzu-support-desk')  ;?>"/>
                   </div>
                  <div class="setting">
                       <label for="ksd_mail_check_freq"><?php _e("Mailbox Check Frequency ( In minutes )","kanzu-support-desk"); ?></label>                       
                       <input name="ksd_mail_check_freq"  type="text" size="30" value="<?php echo $mail_settings['ksd_mail_check_freq']; ?>"  />
                       <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('How often your mailbox should be checked','kanzu-support-desk') ;?>"/>
                   </div> 
                   <div class="setting">
                       <label for="ksd_mail_validate_certificate"><?php _e("Validate Certificate","kanzu-support-desk"); ?></label>                       
                       <input name="ksd_mail_validate_certificate"  type="checkbox" <?php checked( $mail_settings['ksd_mail_validate_certificate'], "yes" ) ?> value="yes"  />
                       <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('Validate your SSL certificate during connection. Use only if you have a valid SSL certificate otherwise it will fail','kanzu-support-desk')  ;?>"/>
                   </div>
                   <div class="setting">
                       <label for="ksd_mail_useSSL"><?php _e("Always use secure connection(SSL)?","kanzu-support-desk"); ?></label>
                       <input name="ksd_mail_useSSL"  type="checkbox" <?php checked( $mail_settings['ksd_mail_useSSL'], "yes" ) ?> value="yes"  />
                       <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php printf( __('Enable use of secure connections (SSL). Note that you will need to use the correct corresponding port. Defaults are %1$d (%2$s) and %3$d (%4$s)','kanzu-support-desk'),993,'IMAP/SSL',995,'POP3/SSL' )  ;?>"/>
                       <input name="ksd_mail_settings_changed" type="hidden" value="no" /> 
                   </div>  
                   <div class="setting">
                       <input class="button-small button button-primary ksd-button" type="button" name="test_mail_connection" value="<?php _e("Test Connection","kanzu-support-desk"); ?>" />
                       <span id="test_mail_connection"></span>
                   </div> 
                </div>
