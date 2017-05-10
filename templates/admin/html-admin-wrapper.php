<div id="admin-kanzu-support-desk" >
    <div class="admin-ksd-title">
        <?php
            $ksd_current_page = str_ireplace( 'ksd-', '', sanitize_key( $_GET['page'] ) );
            $ksd_page = array();//We do this to internationalize the names
            $ksd_page[ 'dashboard' ]['name']  =   __( 'Dashboard','kanzu-support-desk' );
            $ksd_page[ 'settings' ]['name']   =   __( 'Settings','kanzu-support-desk' );
            $ksd_page[ 'feedback' ]['name']   =   __( 'Give Feedback','kanzu-support-desk' );
            $ksd_page[ 'addons' ]['name']     =   __( 'Add-ons','kanzu-support-desk' );

             //Files
            $ksd_page[ 'dashboard' ]['file']  =   'html-admin-dashboard.php' ;
            $ksd_page[ 'settings' ]['file']   =   'html-admin-settings.php';
            $ksd_page[ 'feedback' ]['file']   =   'help-support-tab.php';
            $ksd_page[ 'addons' ]['file']     =   'html-admin-addons.php';

            $ksd_addon_page = array();
            $ksd_addon_page = apply_filters( 'ksd_admin_menu_page', $ksd_addon_page );

            $ksd_page = array_merge( $ksd_page, $ksd_addon_page );

        ?>
        <h2><?php echo $ksd_page[ $ksd_current_page]['name']; ?></h2>
        <span class="more_nav"><img src="<?php echo KSD_PLUGIN_URL. '/assets/images/icons/more_top.png'; ?>" title="<?php _e('Notifications','kanzu-support-desk'); ?>" /></span>
    </div>
    <div class="admin-ksd-container">
        <div id="<?php echo $ksd_current_page;?>" class="admin-ksd-content">
            <?php include_once( $ksd_page[ $ksd_current_page]['file'] ); ?>
        </div>
        <div class="ksd-dialog loading hidden"><?php __( 'Loading...', 'kanzu-support-desk'); ?></div>
        <div class="ksd-dialog error hidden"><?php __( 'Error...', 'kanzu-support-desk'); ?></div>
        <div class="ksd-dialog success hidden"><?php __( 'Success...', 'kanzu-support-desk'); ?></div>
        <div id="ksd-blog-notifications"><?php _e( 'Loading...', 'kanzu-support-desk'); ?></div>
    </div>
</div>
