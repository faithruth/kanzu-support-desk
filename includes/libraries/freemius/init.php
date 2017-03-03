<?php
// Create a helper function for easy Freemium SDK access.
// Special file added for the KSD setup
function ksd_fs() {
    global $ksd_fs;

    if ( ! isset( $ksd_fs ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/start.php';

        $ksd_fs = fs_dynamic_init( array(
            'id'                  => '825',
            'slug'                => 'kanzu-support-desk',
            'type'                => 'plugin',
            'public_key'          => 'pk_1328f7178bcf55b4ef9476475f879',
            'is_premium'          => false,
            'has_premium_version' => false,
            'has_addons'          => false,
            'has_paid_plans'      => false,
            'menu'                => array(
                'slug'           => 'ksd-settings',
                'first-path'     => 'edit.php?post_type=ksd_ticket&ksd_getting_started=1',
                'account'        => false,
                'contact'        => false,
                'support'        => false,
                'parent'         => array(
                    'slug' => 'edit.php?post_type=ksd_ticket',
                ),
            ),
        ) );
    }

    return $ksd_fs;
}

// Init Freemius.
ksd_fs();