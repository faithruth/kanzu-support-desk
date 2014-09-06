<div id="ksd-new-ticket" style="display: none;">
    <h3><?php _e('New Ticket','kanzu-support-desk'); ?></h3>
    <form action="<?php echo admin_url('admin.php?page=ksd-new-ticket'); ?>" id="new-ticket" method="POST">
        <input type="text" value="<?php _e('Customer Name','kanzu-support-desk'); ?>" size="30" name="customer_name" label="Customer Name" class="ksd-customer-name"/>
        <input type="text" value="<?php _e('Customer Email','kanzu-support-desk'); ?>" size="30" name="customer_email" label="Customer Email" class="ksd-customer-email"/>
        <input type="text" value="<?php _e('Subject','kanzu-support-desk'); ?>" maxlength="255" name="subject" label="Subject" class="ksd-subject"/>
        <textarea value="<?php _e('Description','kanzu-support-desk'); ?>" rows="7" class="ksd-description" name="description"></textarea>
        <div class="ksd-severity">
            <label for="severity"><?php _e('Severity','kanzu-support-desk'); ?></label>
            <select name="severity">
                <option>-</option>
                <option><?php _e('URGENT','kanzu-support-desk'); ?></option>
                <option><?php _e('HIGH','kanzu-support-desk'); ?></option>
                <option><?php _e('MEDIUM','kanzu-support-desk'); ?></option>
                <option><?php _e('LOW','kanzu-support-desk'); ?></option>
            </select>
        </div>
        <div class="ksd-assign-to">
            <label for="assign-to"><?php _e('Assign To','kanzu-support-desk'); ?></label>
            <select name="assign-to">
                <option>-</option>
            <?php $agents = get_users();
                foreach ( $agents as $agent ) {
                    echo '<option>' . esc_html( $agent->display_name ) . '</option>';
                }
            ?>
            </select>
        </div> 
        <input type="submit" value="Submit" name="ksd-submit" class="ksd-submit"/>
    </form>
</div>