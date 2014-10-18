<div id="ksd-single-ticket">
    <div class="author_and_subject"><?php  _e('Loading...','kanzu-support-desk');?></div>
    <div class="description pending">
        <?php  _e('Loading...','kanzu-support-desk');?>
    </div>
    <div id="ticket-replies">
        <?php  _e('Loading...','kanzu-support-desk');?>
    </div>
    <div class="edit-ticket">
    <form id="edit-ticket" method="POST">
        <ul class="edit-ticket-options"><li class="reply selected">Reply</li><li class="forward">Forward</li><li class="private-note">Private Note</li></ul>
        <div class="edit-ticket-description">
            <?php /* //wp_editor has a bug that returns stale data
            //$edit_ticket_settings = array ( 'textarea_rows'=> 5, 'media_buttons' => FALSE );
           // wp_editor( __('Reply','kanzu-support-desk'), 'ksd_ticket_reply',$edit_ticket_settings); */?> 
            <textarea name="ksd_ticket_reply" rows="5" cols="100"><?php  _e('Reply','kanzu-support-desk');?></textarea> 
        </div>
        <input name="action" type="hidden" value="ksd_reply_ticket" />
        <input name="tkt_id" type="hidden" value="<?php echo $_GET['ticket'];?>" />        
        <?php wp_nonce_field( 'ksd-edit-ticket', 'edit-ticket-nonce' ); ?>
        <input type="submit" value="<?php  _e('Reply','kanzu-support-desk');?>" name="edit-ticket" id="edit-ticket-submit" class="button button-primary button-large ksd-submit"/>        
    </form>
  </div>
</div>