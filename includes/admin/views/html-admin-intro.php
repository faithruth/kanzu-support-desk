<div id="ksd-intro-one" class="wrap about-wrap">
    <div class="ksd-logo-intro"></div>
    <?php global $current_user; ?>
    <h1>Welcome to Kanzu Support Desk</h1>
    <div class="about-text"><!--@TODO Internationalize this-->
        Hi <?php echo $current_user->display_name; ?>,<br />
        Thanks for choosing <span class="ksd-blue-bold">Kanzu Support Desk (KSD)</span>. We built it to make giving great, personal customer support simpler for you. 
        We focused a lot on simplicity, with the goal of making it as simple to use as WordPress itself.  To get you started, we've added a guided tour. 
        Quick one though – <span class="ksd-blue-bold">why did you choose KSD? What features do you hope to find here?</span> 
        We ask this because it is essential in making sure we deliver on what you want. Hit ‘reply’ below and we’ll get your message. Thanks! 
        <?php echo KSD_Admin::output_feeback_form( 'intro','Reply' ); ?>        
        <br />
        
        <a class="button-primary ksd-start-intro" href="<?php echo admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-dashboard&ksd-intro=1&ksd-onboarding=1' ); ?>">Get Started! </a>
        <p>PS: If you do run into any issues (or have any feedback whatsoever), get in touch on <a href="mailto:feedback@kanzucode.com">feedback@kanzucode.com</a></p>
    </div>
</div>
