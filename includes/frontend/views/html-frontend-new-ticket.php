<button id="ksd-new-ticket-frontend"><?php _e("Support","kanzu-support-desk"); ?></button>
   <div id="ksd-new-ticket-frontend-wrap">
    <form action="#" id="ksd-new-ticket" method="POST">
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
     <!--<textarea value="<?php ; ?>" rows="7" class="ksd-message" name="description"></textarea>-->
          <li class="ksd-message">     
              <label for="tkt_message"><?php _e('Message','kanzu-support-desk'); ?></label>
              <textarea value="" rows="7" class="ksd-message" name="tkt_message" required></textarea>
          </li>
          <input name="action" type="hidden" value="ksd_new_ticket_frontend" />
          <input type="submit" value="<?php _e( "Send Message","kanzu-support-desk" ); ?>" name="ksd-submit" class="ksd-submit"/>
        </ul>
    </form>
</div>