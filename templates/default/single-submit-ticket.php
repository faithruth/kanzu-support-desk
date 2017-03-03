<?php 
$settings = kanzu_support_desk::get_settings();
if( isset( $_GET['ksd_tkt_submitted'] ) ):    
    $response_key = KSD()->session->get( 'ksd_notice' );
    echo "<div class='ksd-support-form-response' >{$settings[$response_key[0]]}</div>";
endif;?>
<div class="ksd-new-ticket-form-wrap <?php echo $form_wrapper_classes; ?>">
        <?php echo $before_form; ?>
        <form method="POST" class="ksd-new-ticket-public <?php echo $form_classes; ?>" enctype="multipart/form-data">
            <ul>      
            <?php if( "no" == $settings['enable_customer_signup'] && ! is_user_logged_in() ): ?>
                <li class="ksd-cust-fullname">       
                  <input type="text" value="<?php _e( 'Name', 'kanzu-support-desk' ); ?>" name="ksd_cust_fullname" label="Name" class="ksd-cust-fullname" minlength="2" required/>
                </li>
                <li class="ksd-customer-email">       
                  <input type="email" value="<?php _e( 'Email', 'kanzu-support-desk' ); ?>" name="ksd_cust_email" label="Email" class="ksd-customer-email" minlength="2" required/>
                </li>    
            <?php endif; ?>                
            <li class="ksd-subject">       
              <input type="text" value="<?php _e( 'Subject', 'kanzu-support-desk' ); ?>" maxlength="255" name="ksd_tkt_subject" label="<?php _e( 'Subject', 'kanzu-support-desk' ); ?>" class="ksd-subject" minlength="2" required/>
            </li>
       <?php
            $show_categories                = $settings['supportform_show_categories'];
            $show_products                  = $settings['supportform_show_products'];
            $show_severity                  = $settings['supportform_show_severity'];  
            
            if( 'yes' === $show_severity ):
            ?>
            <li class="ksd-pdt-severity">  
                <select class="ksd-severity" name="ksd_tkt_severity" >
                    <option selected="selected" disabled="disabled"><?php _e( 'Severity', 'kanzu-support-desk' ); ?></option>
                    <option value="low"><?php echo _e( 'Low', 'kanzu-support-desk' ); ?></option>
                    <option value="medium"><?php echo _e( 'Medium', 'kanzu-support-desk' ); ?></option>
                    <option value="high"><?php echo _e( 'High', 'kanzu-support-desk' ); ?></option>
                    <option value="urgent"><?php echo _e( 'Urgent', 'kanzu-support-desk' ); ?></option>
                </select>
            </li>  
            <?php endif; ?>        
            <?php if( 'yes' === $show_products ): ?>
            <li class="ksd_pdt-categories" >
                <select  name="ksd_tkt_product_id" >
                    <option selected="selected" disabled="disabled"><?php _e( 'Product', 'kanzu-support-desk' ); ?></option>    
                    <?php
                        $products = get_categories( array( 'taxonomy' => 'product', 'hide_empty' => 0 ) );
                        foreach( $products  as $product ){
                            echo "<option value='{$product->term_id}'>{$product->name}</option>";
                        }
                    ?>
                </select>    
            </li>
            <?php endif; ?>     
            <?php if( 'yes' === $show_categories ): ?>
            <li class="ksd-tkt-categories" >
                <select  name="ksd_tkt_cat_id" >
                    <option selected="selected" disabled="disabled"><?php _e( 'Category', 'kanzu-support-desk' ); ?></option>    
                    <?php
                        $cats = get_categories( array(  'taxonomy' => 'ticket_category', 'hide_empty' => 0 ) );
                        foreach( $cats  as $cat ){
                            echo "<option value='{$cat->term_id}'>{$cat->name}</option>";
                        }
                    ?>
                </select>    
            </li>
            <?php endif; ?>    
            
            <?php if ( current_user_can( 'upload_files' ) ): ?>
                <li class="ksd-tkt-attachment" >
                    <a title="<?php _e( 'Add Media','kanzu-support-desk' ); ?>"  class="button insert-media add_media" id="ksd-insert-media-button" href="#"><span class="wp-media-buttons-icon"></span><?php _e( 'Add Media','kanzu-support-desk' ); ?></a>
                    <ul class="ksd_attachments">
                    </ul>
                </li>            
            <?php endif; ?> 
            
             <?php 
             do_action('ksd_add_custom_fields');
             ?>
              <li class="ksd-message">     
                  <textarea value="<?php _e( 'Message','kanzu-support-desk' ); ?>" rows="5" class="ksd-message" name="ksd_tkt_message" id="ksd-ticket-message" placeholder="<?php _e('Message', 'kanzu-support-desk'); ?>"  required></textarea>
              </li>
            <!--Add Google reCAPTCHA-->
            <?php if( "yes" == $settings['enable_recaptcha'] && $settings['recaptcha_site_key'] !== '' ): ?>
                <li class="ksd-g-recaptcha">
                    <span class="ksd-g-recaptcha-error"></span>
                    <div id="<?php echo uniqid( 'g-recaptcha-field-' ); ?>" class="g-recaptcha"></div>
                </li>
            <?php endif; ?>
              <li class="ksd-public-submit">
                <img src="<?php echo KSD_PLUGIN_URL.'assets/images/loading_dialog.gif'; ?>" class="ksd_loading_dialog" width="45" height="35" />
                <input type="submit" value="<?php _e( 'Send Message', 'kanzu-support-desk'); ?>" name="ksd-submit-tab-new-ticket" class="ksd-submit"/>
              </li>
            </ul>
            <input name="action" type="hidden" value="ksd_log_new_ticket" />
            <?php if ( isset( $_GET[ 'woo_order_id' ] ) ): ?>
            <input name="ksd_woo_order_id" type="hidden" value="<?php  echo sanitize_key( $_GET[ 'woo_order_id' ] ); ?>" />
            <?php endif; ?>
            <input name="ksd_tkt_channel" type="hidden" value="support-tab" />
            <?php wp_nonce_field( 'ksd-new-ticket', 'new-ticket-nonce' ); ?>
        </form>
        <div class="ksd-support-form-response"></div>
    </div>