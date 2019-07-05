<?php
/**
 * Hash Url Template
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

?>
<?php $permalink_label = __( 'Permalink', 'kanzu-support-desk' ); ?>
<div class="inside" id="ksd-edit-slug-wrapper">
	<div id="ksd-edit-slug-box" class="hide-if-no-js">
		<strong><?php echo $permalink_label; ?>:&nbsp;</strong><a href="<?php echo $hash_url; ?>"><?php echo $hash_url; ?></a>
	</div>
</div>
