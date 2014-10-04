<div id="ksd-single-ticket">
    <div class="author_and_subject">Peter Kakoma - The subject</div>
    <div class="description">
Convert the pie chart to a bar chart by replacing google.visualization.PieChart with google.visualization.
BarChart in the code and reloading your browser. Convert the pie chart to a bar chart by replacing google.visualization.PieChart with google.visualization.BarChart in the code and reloading your browser. 
    </div>
    <div id="ticket-replies">
    </div>
    <div class="edit-ticket">
    <form  id="edit-ticket"   method="POST"  >
        <ul class="edit-ticket-options"><li class="reply selected">Reply</li><li class="forward">Forward</li><li class="private-note">Private Note</li></ul>
        <div class="edit-ticket-description">
            <?php 
            $edit_ticket_settings = array ( 'textarea_rows'=> 5, 'media_buttons' => FALSE );
            wp_editor( __('Reply','kanzu-support-desk'), 'ksd_ticket_reply',$edit_ticket_settings); ?> 
        </div>
        
        <input name="action" type="hidden" value="ksd_reply_ticket" />
        <input name="tkt_id" type="hidden" value="<?php echo $_GET['ticket'];?>" />        
        <?php wp_nonce_field( 'ksd-edit-ticket', 'edit-ticket-nonce' ); ?>
        <input type="submit" value="Reply" name="edit-ticket" id="edit-ticket-submit" class="button button-primary button-large ksd-submit"/>        
    </form>
  </div>
</div>
