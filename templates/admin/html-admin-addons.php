<?php
/**
 * Admin Addons Template
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

?>
<p><?php esc_html_e( 'Take your Kanzu Support Desk experience to the next level by activating an add-on', 'kanzu-support-desk' ); ?></p>
<?php
do_action( 'ksd_load_addons' );
