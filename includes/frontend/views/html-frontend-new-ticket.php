
<div id="ksd-new-ticket-front">
    <form action="#" id="new-ticket" method="POST">
        <ul>
        <li class="ksd-name">
              <label for="customer_name"><?php _e('Name','kanzu-support-desk'); ?></label>
              <input type="text" value="" size="30" name="customer_name" label="Customer Name" class="ksd-customer-name" minlength="2" required/>
        </li>
        <li class="ksd-email">
              <label for="customer_email"><?php _e('Email','kanzu-support-desk'); ?></label>
              <input type="email" value="" size="30" name="customer_email" label="Customer Email" class="ksd-customer-email" required/>
        </li>        
        <li class="ksd-subject">       
          <label for="tkt_subject"><?php _e('Subject','kanzu-support-desk'); ?></label>
          <input type="text" value="" maxlength="255" name="tkt_subject" label="Subject" class="ksd-subject" minlength="2" required/>
        </li>
     <!--<textarea value="<?php ; ?>" rows="7" class="ksd-description" name="description"></textarea>-->
          <li class="ksd-description">     
              <label for="ksd-ticket-description"><?php _e('Message','kanzu-support-desk'); ?></label>
              <textarea value="" rows="7" class="ksd-description" name="ksd-ticket-description"></textarea>
          </li>
          <input name="tkt_logged_by" type="hidden" value="<?php echo get_current_user_id(); ?>" />
          <input type="submit" value="<?php _e( "Send Message","kanzu-support-desk" ); ?>" name="ksd-submit" class="ksd-submit"/>
        </ul>
    </form>
</div>