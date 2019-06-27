<?php
/**
 * Handle Kanzu Support Desk settings generation
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

namespace Kanzu\Ksd\Admin;

if (!defined('ABSPATH')) {
	exit;
}
// Exit if accessed directly

if (!class_exists('KSD_Settings')):

	class Settings {

		/**
		 * The KSD settings
		 * @since 2.1.0
		 * @var string
		 */
		protected static $settings;

		/**
		 * The key of the add-on whose settings are being processed
		 * @var string
		 */
		protected $addon_key;

		public function __construct() {
			//TODO: Throws PHP Strict Standards Warning:
			//$this->settings = Kanzu_Support_Desk::get_settings();
		}

		/**
		 * Add menu items in the admin panel
		 */
		public function add_menu_pages() {
			//Add the top-level admin menu.
			$page_title = 'Kanzu Support Desk';
			$capability = 'manage_ksd_settings';
			$menu_slug = 'edit.php?post_type=ksd_ticket';
			$function = 'output_admin_menu_dashboard';

			//Add the ticket sub-pages.
			$ticket_types = array();
			$ticket_types['ksd-dashboard'] = __('Dashboard', 'kanzu-support-desk');
			$ticket_types['ksd-settings'] = __('Settings', 'kanzu-support-desk');

			foreach ($ticket_types as $submenu_slug => $submenu_title) {
				$page_hook_suffix = add_submenu_page($menu_slug, $page_title, $submenu_title, $capability, $submenu_slug, array($this, $function));
				add_action('load-' . $page_hook_suffix, array($this, 'add_help_tabs'));
			}

			//Remove ticket tags.
			remove_submenu_page('edit.php?post_type=ksd_ticket', 'edit-tags.php?taxonomy=post_tag&amp;post_type=ksd_ticket');

			//Reset rights in case someone's updating from a very old version.
			$this->reset_user_rights();
		}
		/**
		 * Display the main Kanzu Support Desk admin dashboard
		 */
		public function output_admin_menu_dashboard() {
			$this->do_admin_includes();

			$settings = Kanzu_Support_Desk::get_settings();

			if (isset($_GET['ksd-intro'])) {
				include_once KSD_PLUGIN_DIR . 'templates/admin/html-admin-intro.php';
			} else {
				include_once KSD_PLUGIN_DIR . 'includes/admin/class-ksd-settings.php';
				$addon_settings = new KSD_Settings();
				$addon_settings_html = $addon_settings->generate_addon_settings_html();
				include_once KSD_PLUGIN_DIR . 'templates/admin/html-admin-wrapper.php';
			}
		}

		public function generate_addon_settings_html() {
			$tab_list_html = $tab_div_html = $addon_key = '';
			$all_addon_settings = array();
			$all_addon_settings = apply_filters('ksd_addon_settings', $all_addon_settings);

			foreach ($all_addon_settings as $single_addon_settings):
				foreach ($single_addon_settings as $addon_setting):
					if ('title' == $addon_setting['type']):
						$tab_list_html .= '<li><a href="#' . $addon_setting['id'] . '">' . $addon_setting['label'] . '</a></li>';
						$this->addon_key = $addon_setting['id'];
						$tab_div_html .= '<div id="' . $this->addon_key . '">';
						continue;
					endif;

					$class = (isset($addon_setting['class'])) ? $addon_setting['class'] : '';

					$tab_div_html .= "<div class='setting {$class}'>";
					$tab_div_html .= '<label for="' . $addon_setting['id'] . '">' . $addon_setting['label'] . '</label>';
					$tab_div_html .= $this->generate_single_setting_html($addon_setting);
					$tab_div_html .= $this->generate_tooltip_html($addon_setting);
					$tab_div_html .= $this->add_description_html($addon_setting);
					$tab_div_html .= '</div>';
				endforeach;
				$tab_div_html .= '</div>';
			endforeach;

			return array('tab_html' => $tab_list_html, 'div_html' => $tab_div_html);
		}

		private function generate_single_setting_html($addon_setting) {

			if (method_exists($this, 'generate_' . $addon_setting['type'] . '_html')) {
				return $this->{'generate_' . $addon_setting['type'] . '_html'}($addon_setting);
			}

			return $this->{'generate_text_html'}($addon_setting);
		}

		private function generate_checkbox_html($addon_setting) {
			$checked = (true == $addon_setting['checked']) ? "yes" : 'no';
			return '<input name="' . $addon_setting['id'] . '"  type="checkbox" ' . checked($checked, "yes") . ' value="yes"  />';
		}

		private function generate_tooltip_html($addon_setting) {
			if (isset($addon_setting['tooltip'])) {
				return ' <img width="16" height="16" src="' . KSD_PLUGIN_URL . '/assets/images/help.png" class="help_tip" title="' . $addon_setting['tooltip'] . '"/> ';
			}
		}

		private function generate_raw_html($addon_setting) {
			return $addon_setting['raw_html'];
		}
		/**
		 * Generate HTML for form text field
		 *
		 * @param array $addon_setting{
		 *      @type string id     Name attribute value
		 *      @type string label  Field's label
		 * }
		 *
		 * @return type
		 */
		private function generate_text_html($addon_setting) {
			$placeholder = (isset($addon_setting['placeholder'])) ? "placeholder='{$addon_setting['placeholder']}'" : '';
			$value = (isset($addon_setting['value'])) ? $addon_setting['value'] : '';
			return '<input name="' . $addon_setting['id'] . '"  type="text" value="' . $value . '" size="30" name="' . $addon_setting['id'] . '" ' . $placeholder . ' />';
		}

		/**
		 * Generate HTML for form password field.
		 *
		 * @param array $addon_setting{
		 *      @type string id      name attribute value
		 *      @type string label  Field's label
		 * }
		 * @return string Password form field HTML
		 */
		private function generate_password_html($addon_setting) {
			$placeholder = (isset($addon_setting['placeholder'])) ? "placeholder='{$addon_setting['placeholder']}'" : '';
			$value = (isset($addon_setting['value'])) ? $addon_setting['value'] : '';
			return '<input name="' . $addon_setting['id'] . '"  type="password" value="' . $value . '" size="30" name="' . $addon_setting['id'] . '" ' . $placeholder . ' />';
		}

		/**
		 * Generate radio group options
		 *
		 * @since 2.1.0
		 * @param array $addon_setting {
		 *      @type string id         Radio group name attribute
		 *      @type string label      Settings item label
		 *      @type string type       Form field type. Always "radio".
		 *      @type string tooltip    Optional. Tooltip
		 *      @type class  class      Optional. Custom class to add to settings entry
		 *      @type array  values {
		 *          Array of arguments for all radio group items
		 *
		 *          @type array {
		 *              Array of each radio group item
		 *
		 *              @type string value     Radio group item value
		 *              @type string checked   Optional. Whether the radio group item is checke
		 *              @tpye string label     Label for radio group item
		 *          }
		 *      }
		 * }
		 * @return string Radio group HTML
		 */
		private function generate_radio_html($addon_setting) {
			$radio_group = '';
			$id = $addon_setting['id'];
			$radio_group = "";
			foreach ($addon_setting['values'] as $option) {
				$checked = (isset($option['checked'])) ? 'checked="checked"' : '';
				$radio_group .= "<input type='radio' name='{$id}' value='{$option['value']}' {$checked} />{$option['label']} ";
			}
			return $radio_group;
		}

		/**
		 * Generate HTML for settings form select fields
		 *
		 * @since 2.1.0
		 * @param array $addon_setting {
		 *      @type string id             Radio group name attribute
		 *      @type string label          Settings item label
		 *      @type string type           Form field type. Always "select".
		 *      @type string tooltip        Optional. Tooltip text.
		 *      @type string multiple       Optional. Whether field allows multiple selection. Value is True or False.
		 *      @type class  class          Optional. Custom class to add to settings entry.
		 *      @type array  values {
		 *          Array of arguments for all select options items
		 *
		 *          @type array {
		 *              Array of each select option
		 *
		 *              @type string value     Radio group item value
		 *              @type string checked   Optional. Whether the radio group item is checke
		 *              @tpye string label     Label for radio group item
		 *          }
		 *      }
		 * }
		 * @return string select field HTML
		 */
		private function generate_select_html($addon_setting) {
			$select_html = '';
			$id = $addon_setting['id'];
			$multiselect = (isset($addon_setting['multiple']) && true === $addon_setting['multiple']) ? "multiple'" : "";
			$select_html = "<select name='{$id}' {$multiselect}>";
			$checked = "";
			foreach ($addon_setting['options'] as $option) {
				$selected = (isset($option['selected']) && 'selected' === $option['selected']) ? 'selected="selected"' : '';
				$select_html .= "<option value='{$option['value']}' {$checked} >{$option['label']}</option>";
			}
			$select_html .= '</select>';
			return $select_html;
		}

		/**
		 * Generate HTML for settings form file fields
		 *
		 * @since 2.1.0
		 * @param array $addon_setting {
		 *      @type string id             Radio group name attribute
		 *      @type string label          Settings item label
		 *      @type string type           Form field type. Always "file".
		 *      @type string tooltip        Optional. Tooltip text.
		 *      @type class  class          Optional. Custom class to add to settings entry.
		 * }
		 * @return string file field HTML
		 */
		public function generate_file_html($addon_setting) {
			$html = "<input name='{$addon_setting['id']}' type='file' />";
			return $html;
		}

		//TODO: This should be changed to textarea to cover abroader set of use cases other than description
		private function add_description_html($addon_setting) {
			if (isset($addon_setting['description'])) {
				return ' <span class="description">' . $addon_setting['description'] . '</span>';
			}
			return '';
		}
		/**
		 * Generate HTML for settings link
		 * @param array $addon_setting {
		 *      @type string id             anchor name attribute
		 *      @type string text           anchor text
		 *      @type string href           anchor hyperlink reference
		 *      @type string link_class     anchor class attribute
		 * }
		 *
		 */
		private function generate_link_html($addon_setting) {
			$link_html = "<a name='{$addon_setting['id']}' class='{$addon_setting['link_class']}' href='{$addon_setting['href']}' >{$addon_setting['text']}</a>";
			return $link_html;
		}
	}
endif;
