<div id="ksd-new-ticket">
    <form action="<?php echo admin_url('admin.php?page=ksd-new-ticket'); ?>" id="new-ticket" method="POST">
        <div>
            <input type="text" value="<?php _e('Customer Name','kanzu-support-desk'); ?>" size="30" name="customer_name" label="Customer Name" class="ksd-customer-name" minlength="2" required/>
            <input type="email" value="<?php _e('Customer Email','kanzu-support-desk'); ?>" size="30" name="customer_email" label="Customer Email" class="ksd-customer-email" required/>
            <input type="text" value="<?php _e('Subject','kanzu-support-desk'); ?>" maxlength="255" name="tkt_subject" label="Subject" class="ksd-subject" minlength="2" required/>
            <!--<textarea value="<?php ; ?>" rows="7" class="ksd-description" name="description"></textarea>-->
        </div>
        <div class="ksd-description">
            <?php wp_editor(  '' , 'ksd-ticket-description', array( "media_buttons" => false, "textarea_rows" => 5 ) ); ?> 
        </div>
        <div class="ksd-severity">
            <label for="tkt_severity"><?php _e('Severity','kanzu-support-desk'); ?></label>
            <select name="tkt_severity">
                <option><?php _e('LOW','kanzu-support-desk'); ?></option>
                <option><?php _e('MEDIUM','kanzu-support-desk'); ?></option>
                <option><?php _e('HIGH','kanzu-support-desk'); ?></option>
                <option><?php _e('URGENT','kanzu-support-desk'); ?></option>
            </select>
        </div>
        <div class="ksd-assign-to">
            <label for="assign-to"><?php _e('Assign To','kanzu-support-desk'); ?></label>
            <select name="assign-to">
                <option>-</option>
            <?php $agents = get_users();
                foreach ( $agents as $agent ) {
                    echo '<option value='.$agent->ID.'>' . esc_html( $agent->display_name ) . '</option>';
                }
            ?>
            </select>
        </div> 
        <input name="tkt_logged_by" type="hidden" value="<?php echo get_current_user_id(); ?>" />
        <input type="submit" value="<?php _e( "Submit","kanzu-support-desk" ); ?>" name="ksd-submit" class="ksd-submit"/>
    </form>
</div>