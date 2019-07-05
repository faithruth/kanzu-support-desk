<?php
/**
 * Admin Dashboard Template
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

?>
<ul class="dashboard-statistics-summary pending">
		<li> <?php esc_html_e( 'Loading...', 'kanzu-support-desk' ); ?></li>
</ul>

<div id="ksd_dashboard_chart"> <!--Ticket Inflow-->
</div>

<!--Tickets by status-->     <!--Tickets by channel-->


<!--Top Agents-->

<script type="text/javascript">
	jQuery(function(){
		jQuery('.admin-ksd-title h2').html('<?php esc_html_e( 'Dashboard', 'kanzu-support-desk' ); ?>');
	});
</script>
