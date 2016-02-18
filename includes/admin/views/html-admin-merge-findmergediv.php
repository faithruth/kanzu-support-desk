<div id="find-posts" class="find-box" style="display: none;">
    <div id="find-posts-head" class="find-box-head">
        <?php _e( 'Select ticket to merge into' , 'kanzu-support-desk' ); ?>
        <div id="find-posts-close"></div>
    </div>
    <div class="find-box-inside">
        <div class="find-box-search">
            <input type="hidden" name="found_action" value="" />
            <input type="hidden" name="affected" id="affected" value="" />
            <?php wp_nonce_field( 'find-posts', '_ajax_nonce', false ); ?>
            <label class="screen-reader-text" for="find-posts-input"><?php _e( 'Search' ); ?></label>
            <input type="text" id="find-posts-input" name="ps" value="" />
            <span class="spinner"></span>
            <input type="button" id="find-posts-search-merge" value="<?php esc_attr_e( 'Search' ); ?>" class="button" exclude_posts="" />
            <div class="clear"></div>
        </div>
        <div id="find-posts-response"></div>
    </div>
    <div class="find-box-buttons">
        <?php submit_button( __( 'Merge', 'kanzu-support-desk' ), 'button-primary alignright', 'find-posts-submit-merge', false ); ?>
        <div class="clear"></div>
    </div>
</div>