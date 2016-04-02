<div id="ksd-intro-one" class="wrap about-wrap">
    <div class="ksd-logo-intro"></div>
    <?php global $current_user; ?>
    <h1><?php _e( 'Welcome to Kanzu Support Desk', 'kanzu-support-desk'); ?></h1>
    <div class="about-text">  
    <?php       printf( '%1$s %2$s,<br />%3$s<span class="ksd-blue-bold">%4$s</span>.%5$s<span class="ksd-blue-bold">%6$s</span> %7$s',
            __('Hi','kanzu-support-desk'),
            $current_user->display_name,
            __( 'Thanks for choosing ', 'kanzu-support-desk'),
            __( ' Kanzu Support Desk (KSD)', 'kanzu-support-desk'),
            __( 'We built it to make giving great, personal customer support simpler for you. 
                We focused a lot on simplicity, with the goal of making it as simple to use as WordPress itself.  To get you started, we have added a guided tour. 
                Quick one though – ', 'kanzu-support-desk'),
            __('why did you choose KSD? What features do you hope to find here?','kanzu-support-desk'),
            __('We ask this because it is essential in making sure we deliver on what you want. Hit "reply" below and we’ll get your message. Thanks!','kanzu-support-desk')
                    );
        ?>            
        <?php echo KSD_Admin::output_feeback_form( 'intro','Reply' ); ?>        
        <br />
        <a class="button-primary ksd-start-intro" href="<?php echo admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-dashboard&ksd-intro=1&ksd-onboarding=1' ); ?>"><?php _e( 'Get Started!', 'kanzu-support-desk'); ?> </a>
        <p><?php _e( 'PS: If you do run into any issues (or have any feedback whatsoever), get in touch on ', 'kanzu-support-desk'); ?><a href="mailto:feedback@kanzucode.com">feedback@kanzucode.com</a></p>
    </div>
</div>
