<?php
$settings              = Kanzu_Support_Desk::get_settings ( );
$settings_and_licenses = apply_filters ( 'ksd_display_licenses', $settings );
$licenses              = (  isset ( $settings_and_licenses['licenses'] ) ? $settings_and_licenses['licenses'] : array ( ) );

?>
<form method="POST" id="update-settings" class="ksd-settings pending">
    <div id="ksd-tabs">
        <ul>
            <li><a href="#ksd-general-opt-tab"><?php _e ( 'General', 'kanzu-support-desk' ); ?></a></li>
            <li><a href="#ksd-support-form-settings"><?php _e ( 'Support Form', 'kanzu-support-desk' ); ?></a></li>
            <?php if ( count ( $licenses ) > 0 ): ?><li><a href="#ksd-licences-opt-tab"><?php _e ( 'Licenses', 'kanzu-support-desk' ); ?></a></li> <?php endif; ?>
            <?php echo $addon_settings_html['tab_html']; ?>
        </ul>

        <!-- fragment-1 -->
        <div id="ksd-general-opt-tab">
            <div>
                <h3><?php _e ( 'General Options', 'kanzu-support-desk' ); ?> </h3>
                <div class="setting">
                    <label for="enable_new_tkt_notifxns"><?php _e ( 'Enable auto-reply', 'kanzu-support-desk' ); ?></label>
                    <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( "If enabled, a reply is sent to the customer's email address when he/she logs a new ticket on your site", 'kanzu-support-desk' ); ?>"/>
                    <input name="enable_new_tkt_notifxns"  type="checkbox" <?php checked ( $settings['enable_new_tkt_notifxns'], "yes") ?> value="yes"  />
                </div>
                <div class="enable_new_tkt_notifxns">
                    <div class="setting">
                        <label for="ticket_mail_from_name"><?php _e ( 'From ( Name )', 'kanzu-support-desk' ); ?></label>
                        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( "Defaults to the primary administrator's display name", 'kanzu-support-desk' ); ?>"/>
                        <input type="text" value="<?php echo $settings['ticket_mail_from_name']; ?>" size="30" name="ticket_mail_from_name" />
                    </div>
                    <div class="setting">
                        <label for="ticket_mail_from_email"><?php _e ( 'From ( Email Address )', 'kanzu-support-desk' ); ?></label>
                        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( "Defaults to the primary administrator's email address", 'kanzu-support-desk' ); ?>"/>
                        <input type="text" value="<?php echo $settings['ticket_mail_from_email']; ?>" size="30" name="ticket_mail_from_email" />
                    </div>
                    <div class="setting">
                        <label for="ticket_mail_message"><?php _e ( 'Message', 'kanzu-support-desk' ); ?></label>
                        <textarea cols="60" rows="4" name="ticket_mail_message"><?php echo $settings['ticket_mail_message']; ?></textarea>
                    </div>
                </div><!--.enable_new_tkt_notifxns-->
                <!--<div class="setting enable_notify_on_new_ticket">
                      <label for="notify_email"><?php _e ( 'Send notifications to', 'kanzu-support-desk' ); ?></label>
                      <input type="text" value="<?php echo $settings['notify_email']; ?>" size="30" name="notify_email" />
                      <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( "We send a notification to this email address when a new ticket is created", 'kanzu-support-desk' ); ?>"/>
                </div>-->
                <div class="setting">
                    <label for="auto_assign_user"><?php _e ( 'Auto-assign new tickets to', 'kanzu-support-desk' ); ?></label>
                    <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( 'If set, new tickets are automatically assigned to this user.', 'kanzu-support-desk' ); ?>"/>
                    <select name="auto_assign_user">
                        <?php foreach ( get_users ( array( 'role__in' => array('ksd_agent','ksd_supervisor','administrator' ) ) ) as $agent ) { ?>
                            <option value="<?php echo $agent->ID; ?>"
                                    <?php selected ( $agent->ID, $settings['auto_assign_user']); ?>>
                                        <?php echo $agent->display_name; ?>
                            </option>
                        <?php } ?>
                        <option value=""><?php _e( 'No One', 'kanzu-support-desk' ); ?></option>
                    </select>
                </div>
                <?php if ( class_exists( 'WooCommerce' ) ): ?>
                    <div class="setting">
                        <label for="show_woo_support_tickets_tab"><?php _e ( 'Show Support Tickets tab in WooCommerce', 'kanzu-support-desk' ); ?></label>
                        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( "If enabled, the support tickets associated with a WooCommerce product will be shown in the WooCommerce single product view.", 'kanzu-support-desk' ); ?>"/>
                        <input name="show_woo_support_tickets_tab"  type="checkbox" <?php checked ( $settings['show_woo_support_tickets_tab'], "yes") ?> value="yes"  />
                    </div>
                <?php endif; ?>
                <div id="ksd-below-settings">
                    <div class="ksd-section">
                        <h3><?php _e ( 'Roles', 'kanzu-support-desk' ); ?></h3>
                        <h4><?php _e ( 'Your Agents', 'kanzu-support-desk' ); ?></h4><img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php printf ( __( 'Agents are stored as WordPress users with role %s' , 'kanzu-support-desk' ), 'KSD Agent' ); ?>"/>
                        <a href="user-new.php" class="button button-secondary button-large"> <?php _e ( 'Add New Agent', 'kanzu-support-desk' ); ?></a>
                        <a href="users.php?role=ksd_agent" class="button button-secondary button-large"> <?php _e ( 'View Agents', 'kanzu-support-desk' ); ?></a>
                        <h4><?php _e ( 'Your Supervisors', 'kanzu-support-desk' ); ?></h4><img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php printf ( __ ( 'Supervisors are stored as WordPress users with role %s', 'kanzu-support-desk' ), 'KSD Supervisor' ); ?>"/>
                        <a href="user-new.php" class="button button-secondary button-large"> <?php _e ( 'Add New Supervisor', 'kanzu-support-desk' ); ?></a>
                        <a href="users.php?role=ksd_supervisor" class="button button-secondary button-large"> <?php _e ( 'View Supervisors', 'kanzu-support-desk' ); ?></a>
                        <h4><?php _e ( 'Your Customers', 'kanzu-support-desk' ); ?></h4><img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php printf ( __ ( 'Your customers are stored as WordPress users with role %s' , 'kanzu-support-desk' ), 'KSD Customer' ); ?>"/>
                        <a href="user-new.php" target="_blank" class="button button-secondary button-large"> <?php _e ( 'Add New Customer', 'kanzu-support-desk' ); ?></a>
                        <a href="users.php?role=ksd_customer" target="_blank" class="button button-secondary button-large"> <?php _e ( 'View Customers', 'kanzu-support-desk' ); ?></a>
                    </div>
                    <div class="ksd-section ksd-debug">
                        <div class="ksd-customer-header">
                            <h3><?php _e ( 'Debug', 'kanzu-support-desk' ); ?></h3>
                        </div>
                        <div class="ksd-debug-email">
                            <input type="text" value="" placeholder="<?php _e( 'Send test email to...', 'kanzu-support-desk' ); ?>" size="30" name="debug_test_email" /><a class="button button-secondary" href="#" id="ksd-send-test-email"><?php _e( 'Send test email', 'kanzu-support-desk' ); ?></a>
                            <div id="ksd-debug-email-response"></div>
                        </div>
                        <div class="ksd-debug-generate-file">
                            <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( 'If you contact our support team and you are asked for a debug file, use this button to generate one', 'kanzu-support-desk' ); ?>"/>
                            <a class="button action button-large" id="ksd-debug" href="<?php echo admin_url ( 'admin.php?page=kanzu-support-desk&ksd_action=ksd_generate_debug_file' ); ?>"><?php _e ( "Generate Debug File", "kanzu-support-desk" ); ?> </a>
                        </div>
                        <div class="ksd-debug-reset-role-caps">
                            <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( 'If some agents do not have the Tickets menu or a role does not have capabilities it should have, use this to perform a reset', 'kanzu-support-desk' ); ?>"/>
                           <button class="button action button-large" id="ksd-reset-role-caps"><?php _e ( "Reset role capabilities", "kanzu-support-desk" ); ?> </button>
                           <div id="ksd-debug-reset-role-caps-response"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!--//fragment-1 -->



        <!-- Support Form -->
        <div id="ksd-support-form-settings">
            <?php
            include (  KSD_PLUGIN_DIR . '/templates/admin/html-admin-settings-supportform.php' );
            ?>
        </div>

        <?php if ( count ( $licenses ) > 0 ): ?>
            <div id="ksd-licences-opt-tab"><!--fragment-2 -->
                <h3><?php _e ( 'Licence Management', 'kanzu-support-desk' ); ?> </h3>
                <div>
                    <?php
                    //Iterate through the licenses and display them
                    foreach ( $licenses as $license_details ):
                        ?>
                        <div class="setting">
                            <label for="<?php echo $license_details['license_db_key']; ?>"><?php echo $license_details['addon_name']; ?></label>
                            <input type="text" value="<?php echo $license_details['license']; ?>" size="30" name="<?php echo $license_details['license_db_key']; ?>" />
                            <?php if ( $license_details['license_status'] == 'valid' ) { ?>
                                <span class="license_status valid"><?php _e ( 'active', 'kanzu-support-desk' ); ?></span>
                                <input type="submit" class="button-secondary ksd-license ksd-deactivate_license" name="<?php echo $license_details['license_status_db_key']; ?>" value="<?php _e ( 'Deactivate License', 'kanzu-support-desk' ); ?>"/>
                            <?php } else { ?>
                                <span class="license_status <?php echo $license_details['license_status']; ?>"><?php echo (  empty ( $license_details['license']) ? __ ( 'not set', 'kanzu-support-desk' ) : __ ( 'invalid', 'kanzu-support-desk' ) ); ?></span>
                                <input type="submit" class="button-secondary ksd-license ksd-activate_license" name="<?php echo $license_details['license_status_db_key']; ?>" value="<?php _e ( 'Activate License', 'kanzu-support-desk' ); ?>"/>
                            <?php } ?>
                            <span class="plugin_name hidden"><?php echo $license_details['plugin_name'] ?></span>
                            <span class="plugin_author_uri hidden"><?php echo $license_details['plugin_author_uri'] ?></span>
                            <span class="plugin_options_key hidden"><?php echo $license_details['plugin_options_key'] ?></span>
                        </div>
                    <?php endforeach;
                    ?>
                </div>
            </div><!--//fragment-2 -->
            <?php echo $addon_settings_html['div_html']; ?>
        </div>
<?php endif; ?>

    <input name="action" type="hidden" value="ksd_update_settings" />
<?php wp_nonce_field ( 'ksd-update-settings', 'update-settings-nonce' ); ?>
    <input type="submit" value="<?php _e ( 'Update', 'kanzu-support-desk' ); ?>" name="ksd-settings-submit" class="ksd-submit button button-primary button-large"/>
    <input type="submit" value="<?php _e ( 'Reset to Defaults', 'kanzu-support-desk' ); ?>" name="ksd-settings-reset" class="ksd-submit ksd-reset button action button-large"/>

    <script type="text/javascript">
        jQuery ( '#ksd-tabs' ).tabs ( );
    </script>
</form>
