<div id="ksd-merge-ticket-wrap" style="display: none;" role="dialog" title="<?php _e( 'Merge ticket into current ticket', 'kanzu-support-desk'  ) ?>">
    <form id="ksd-merge-ticket" tabindex="-1">
        <?php wp_nonce_field( 'ksd-merging', '_ajax_ksd_merging_nonce', false ); ?>
        <div id="ksd-merge-selector">         
            <div id="ksd-merge-options">
                <?php _e( 'Current ticket:', 'kanzu-support-desk' );?>
                <div class="ksd-merge-parent-ticket-details">
                    <span id="ksd-merge-parent-ticket-id">#<?php echo sanitize_key( $_GET['post'] ); ?> </span><span id="ksd-merge-parent-ticket-title"></span>
                </div>
            </div>
            <div class="ksd-merge-ticket-merge-wrap hidden">
                <div class="ksd-merge-how-to" id="ksd-merge-existing-content">
					<img width = "48" height = "48"  src = "<?php echo KSD_PLUGIN_URL.'/assets/images/icons/addition.png'; ?>" />
                </div>
                <span id="ksd-merge-merge-ticket-title"></span> 
                <input id="ksd-merge-merge-ticket-id" name="ksd-merge-merge-ticket-id" type="hidden" value="0" />
                <div class="ksd-merge-ticket-merge-button-wraper">
                	<button type="button" class="button button-large" id="ksd-merge-ticket-merge-button"><?php _e( 'Merge', 'kanzu-support-desk'  ); ?></button>
                </div>
            </div>             
            <div class="submitbox">
                <div id="ksd-merge-ticket-select" class="hidden">
                    <em><?php _e( 'A merge cannot be undone','kanzu-support-desk' ); ?></em>
                    <div class="confirm-merge-wrapper">
	                    <input type="submit" value="<?php esc_attr_e( 'Confirm Merge', 'kanzu-support-desk'  ); ?>" class="button button-primary" id="ksd-merge-ticket-confirm" name="ksd-merge-ticket-confirm">
	                    <input type="button" class="button button-large" id="ksd-merge-cancel" value="<?php esc_attr_e( 'Cancel', 'kanzu-support-desk'  ); ?>"/>
                    </div>
                </div>            
            </div>    
            <div id="ksd-merge-final-response" class="empty"></div>
            <div id="ksd-merge-search-panel">
                <span class="search-label"><?php _e( 'Search for ticket to merge', 'kanzu-support-desk'  ); ?></span>
                <input type="text" id="ksd-merge-ticket-search-text" name="ksd-merge-ticket-search" placeholder="<?php _e( 'Search...', 'kanzu-support-desk' ); ?>" /><button class="button button-small" id="ksd-merge-ticket-search"><?php _e( 'Search', 'kanzu-support-desk' );?></button>
            </div>
            <div id="ksd-merge-most-recent-results" class="query-results" tabindex="0">
                <span class="spinner ksd-merge-spinner hidden"></span>
                <ul class="ksd-merge-tickets-list">
                    <li>                    
                        <em class="query-notice-default"><?php _e( 'No search term specified. Search for a ticket to merge', 'kanzu-support-desk'  ); ?></em>
                        <em class="query-notice-hint screen-reader-text"><?php _e( 'Search or use up and down arrow keys to select an item.', 'kanzu-support-desk'  ); ?></em>
                    </li>
                </ul>
            </div>            
        </div>
        <input type="hidden" value="<?php echo sanitize_key( $_GET['post'] ); ?>" name="ksd-merge-parent-ticket"/>    
    </form>
</div>