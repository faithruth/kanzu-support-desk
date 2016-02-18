<form name="plugin_form" id="plugin_form" method="post" action="">
    <?php wp_nonce_field( 'ksd_admin_nonce', 'ksd-admin-nonce' ); ?> 
    <?php KSD_Admin::find_merge_div(); ?>
</form>