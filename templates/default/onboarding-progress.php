<?php
/**
 * Onboarding Process template
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

?>
<div class="ksd-onboarding-progress wp-core-ui">
<ol class="ksd-onboarding-stages">
				<li class="done"><?php esc_htmlesc_html_e( 'Start tour', 'kanzu-support-desk' ); ?> </li>
				<li class="active"><?php esc_html_e( 'Create ticket', 'kanzu-support-desk' ); ?> </li>
				<li class=""><?php esc_html_e( 'Reply ticket', 'kanzu-support-desk' ); ?></li>
				<li class=""><?php esc_html_e( 'Resolve ticket', 'kanzu-support-desk' ); ?></li>
				<li class=""><?php esc_html_e( 'Assign ticket', 'kanzu-support-desk' ); ?></li>
				<li class=""><?php esc_html_e( 'Ready!', 'kanzu-support-desk' ); ?></li>
</ol>
	<div class="ksd-onboarding-notes"></div>
	<a href="<?php echo wp_kses_post( admin_url( 'edit.php?post_type=ksd_ticket&ksd-onboarding=3' ) ); ?>" class="button-large button button-primary ksd-onboarding-next">Next</a>

</div>

