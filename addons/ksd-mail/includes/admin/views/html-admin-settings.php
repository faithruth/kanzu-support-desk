<?php 

$mail_settings = $current_settings[KSD_OPTIONS_KEY];

?>

<h3><?php _e("Mail","ksd-mail"); ?> </h3>
                <div>
                   <div class="setting">
                       <label for="ksd_mail_server">Mail Server</label>
                       <input type="text" value="<?php echo $mail_settings['ksd_mail_server']; ?>" size="30" name="ksd_mail_server" />
                   </div>
                   <div class="setting">
                       <label for="ksd_mail_account">Support Email Address</label>
                       <input type="text" value="<?php echo $mail_settings['ksd_mail_account']; ?>" size="30" name="ksd_mail_account" />
                   </div>
                   <div class="setting">
                       <label for="ksd_mail_password">Password</label>
                       <input type="password"  size="30" name="ksd_mail_password" value="<?php echo $mail_settings['ksd_mail_password']; ?>"/>
                   </div>
                   <div class="setting">
                       <label for="ksd_mail_protocol">Protocol</label>
                       <select name="ksd_mail_protocol">
                           <option value="pop3" <?php selected( "pop3", $mail_settings['ksd_mail_protocol'] ) ?>>POP3</option>
                           <option value="imap"  <?php selected( "imap", $mail_settings['ksd_mail_protocol'] ) ?> >IMAP</option>
                       </select>
                   </div> 
                       <div class="setting">
                       <label for="ksd_mail_port">Port</label>
                       <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('Default Ports are: 143 (IMAP), 993 (IMAP/SSL), 110 (POP3) and 995 (POP3/SSL)','kanzu-support-desk')  ;?>"/>
                       <input type="text" value="<?php echo $mail_settings['ksd_mail_port']; ?>" size="30" name="ksd_mail_port" />
                   </div> 
                   <div class="setting">
                       <label for="ksd_mail_mailbox">Mailbox</label>
                       <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('The mailbox to query. You almost never need to change this','kanzu-support-desk')  ;?>"/>
                       <input type="text" value="<?php echo $mail_settings['ksd_mail_mailbox']; ?>" size="30" name="ksd_mail_mailbox" />
                   </div>
                  <div class="setting">
                       <label for="ksd_mail_check_freq">Mailbox Check Frequency ( In minutes ) </label>
                       <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('How often your mailbox should be checked','ksd-mail') ;?>"/>
                       <input name="ksd_mail_check_freq"  type="text" size="30" value="<?php echo $mail_settings['ksd_mail_check_freq']; ?>"  />
                   </div> 
                   <div class="setting">
                       <label for="ksd_mail_validate_certificate">Validate Certificate</label>
                       <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('Validate your SSL certificate during connection. Use only if you have a valid SSL certificate otherwise it will fail','kanzu-support-desk')  ;?>"/>
                       <input name="ksd_mail_validate_certificate"  type="checkbox" <?php checked( $mail_settings['ksd_mail_validate_certificate'], "yes" ) ?> value="yes"  />
                   </div> 
                   <div class="setting">
                       <label for="ksd_mail_useSSL">Always use secure connection(SSL)?</label>
                       <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('Enable use of secure connections (SSL). Note that you will need to use the correct corresponding port. Defaults are 993 (IMAP/SSL) and 995 (POP3/SSL)','kanzu-support-desk')  ;?>"/>
                       <input name="ksd_mail_useSSL"  type="checkbox" <?php checked( $mail_settings['ksd_mail_useSSL'], "yes" ) ?> value="yes"  />
                   </div> 
                </div>
