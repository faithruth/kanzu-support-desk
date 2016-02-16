<div>
    <h3><?php _e ( 'Customer Support Form Options', 'kanzu-support-desk' ); ?> </h3>
    <div class="setting">
        <label for="show_support_tab"><?php _e ( 'Show support button', 'kanzu-support-desk' ); ?></label>
        <input name="show_support_tab"  type="checkbox" <?php checked ( $settings['show_support_tab'], "yes") ?> value="yes"  />
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( 'When enabled, shows a support button in the bottom left of your site which your customers can use to log new tickets', 'kanzu-support-desk' ); ?>"/>
    </div>
    <div class="setting show_support_tab">
        <label for="support_button_text"><?php _e ( 'Support button text', 'kanzu-support-desk' ); ?></label>
        <input type="text" value="<?php echo $settings['support_button_text']; ?>" size="15" name="support_button_text"/>
    </div>
    <div class="setting">
        <label for="supportform_show_categories"><?php _e ( 'Show product categories', 'kanzu-support-desk' ); ?></label>
        <input name="supportform_show_categories"  type="checkbox" <?php checked ( $settings['supportform_show_categories'], "yes") ?> value="yes"  />
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( 'When enabled, a ticket category option is added to the support form.', 'kanzu-support-desk' ); ?>"/>
    </div>
    <div class="setting">
        <label for="supportform_show_severity"><?php _e ( 'Show ticket severities', 'kanzu-support-desk' ); ?></label>
        <input name="supportform_show_severity"  type="checkbox" <?php checked ( $settings['supportform_show_severity'], "yes") ?> value="yes"  />
        <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL . "/assets/images/help.png"; ?>" class="help_tip" title="<?php _e ( 'When added a ticket severity option is added to the support form.', 'kanzu-support-desk' ); ?>"/>
    </div>
</div>