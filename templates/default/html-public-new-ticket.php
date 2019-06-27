<?php
/*
 * Template for the new ticket form that slides in and out when the public
 * support button is clicked
 * New ticket forms added using the [ksd_support_form] shortcode
 * use the template in templates/{ActiveKSDTheme}/single-submit-ticket.php. By default, this is template/default/single-submit-ticket.php
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @since 2.0.0
 */
?>
<div class="ksd-new-ticket-form-wrap ksd-form-hidden-tab hidden">
<?php
if (isset($_GET['ksd_tkt_submitted'])):
	$response_key = KSD()->session->get('ksd_notice');
	echo "<div class='ksd-support-form-response' >{$settings[$response_key[0]]}</div>";
endif;
?>
    <form method="POST" class="ksd-new-ticket-public ksd-form-hidden-tab-form" enctype="multipart/form-data">
        <ul>
        <?php KSD()->templates->get_template_part('list', 'ticket-table'); ?>
        
              <li class="ksd-public-submit">
                <img src="<?php echo KSD_PLUGIN_URL . 'assets/images/loading_dialog.gif'; ?>" class="ksd_loading_dialog" width="45" height="35" />
                <input type="submit" value="<?php _e('Send Message', 'kanzu-support-desk');?>" name="ksd-submit-tab-new-ticket" class="ksd-submit"/>
              </li>
            </ul>
            <input name="action" type="hidden" value="ksd_log_new_ticket" />
            <input name="ksd_tkt_channel" type="hidden" value="support-tab" />
            <?php wp_nonce_field('ksd-new-ticket', 'new-ticket-nonce');?>
    </form>
    <div class="ksd-form-hidden-tab-form-response ksd-support-form-response"></div>
</div>
