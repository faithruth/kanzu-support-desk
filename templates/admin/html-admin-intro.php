<div id="ksd-intro-one" class="wrap about-wrap">
    <div class="ksd-logo-intro"></div>
    <?php global $current_user; ?>
    <h1><?php _e( 'Welcome to Kanzu Support Desk', 'kanzu-support-desk'); ?></h1>
    <div class="about-text">  
    <?php       printf( '%1$s %2$s,<br />%3$s<span class="ksd-blue-bold">%4$s</span>.%5$s<div>%6$s</div>',
            __('Hi','kanzu-support-desk'),
            $current_user->display_name,
            __( 'Thanks for choosing ', 'kanzu-support-desk'),
            __( ' Kanzu Support Desk (KSD)', 'kanzu-support-desk'),
            __( 'We built it to make giving great, personal customer support simpler for you. We focused a lot on simplicity, with the goal of making it as simple to use as WordPress itself.', 'kanzu-support-desk'),
            __( 'To get you started, let us show you around. First, you will create a ticket...', 'kanzu-support-desk')            
                    );
        ?>            
 
        <br />
        <a class="button-primary ksd-start-intro" href="<?php echo add_query_arg( 'ksd-onboarding', 'create-ticket', get_permalink( $settings['page_submit_ticket'] ) ); ?>"><?php _e( 'Get Started!', 'kanzu-support-desk'); ?> </a>
    </div>
</div>
