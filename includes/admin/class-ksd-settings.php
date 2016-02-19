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

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Settings' ) ) :

class KSD_Settings {
    
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

        public function __construct(){            
            $this->settings = Kanzu_Support_Desk::get_settings();        
        }
        
        public function generate_addon_settings_html(){
            $tab_list_html = $tab_div_html   = $addon_key = '';
            $all_addon_settings = array ();
            $all_addon_settings = apply_filters ( 'ksd_addon_settings', $all_addon_settings );  
            
            foreach ( $all_addon_settings as $single_addon_settings ): 
                    foreach ( $single_addon_settings as $addon_setting ): 
                        if ( 'title' == $addon_setting['type'] ):  
                            $tab_list_html .='<li><a href="#'.$addon_setting['id'].'">'.$addon_setting['label'].'</a></li>';
                            $this->addon_key = $addon_setting['id'];
                            $tab_div_html .= '<div id="'.$this->addon_key.'">'; 
                            continue;
                        endif;                          
                        $tab_div_html .= '<div class="setting">';             
                        $tab_div_html .= '<label for="'.$addon_setting['id'].'">'.$addon_setting['label'].'</label>';                       
                        $tab_div_html .= $this->generate_single_setting_html( $addon_setting );
                        $tab_div_html .= $this->add_description_html( $addon_setting );
                        $tab_div_html .= '</div>';    
                    endforeach;                      
            endforeach;               
                 $tab_div_html .= '</div>';    
            return  array( 'tab_html' => $tab_list_html, 'div_html' => $tab_div_html )  ;  
        }
        
        private function generate_single_setting_html( $addon_setting ){
            
            if ( method_exists( $this, 'generate_' . $addon_setting['type'] . '_html' ) ) {
                return $this->{'generate_' . $addon_setting['type'] . '_html'}( $addon_setting );
            }  
            
            return $this->{'generate_text_html'}( $addon_setting );            
        }
        
        private function generate_checkbox_html( $addon_setting ){
            return '<input name="'.$addon_setting['id'].'"  type="checkbox" '.  checked( $this->settings[$this->addon_key][ $addon_setting['id'] ], "yes" ) .' value="yes"  />';
        }
        
        
        private function generate_tooltip_html( $addon_setting ){
            return '<img width="16" height="16" src="'. KSD_PLUGIN_URL.'"/assets/images/help.png" class="help_tip" title="'.$addon_setting['label'].'"/>';            
        }
        
        private function generate_text_html( $addon_setting ){
            return '<input name="'.$addon_setting['id'].'"  type="text" value="'. $this->settings[$this->addon_key][ $addon_setting['id'] ] .'" size="30" name="'.$addon_setting['id'].'"  />';
        }        
        
        private function add_description_html( $addon_setting ){
            if( isset( $addon_setting['description'] ) ){
                return '<span class="description">'.$addon_setting['description'].'</span>';
            }
            return '';
        }
        
             
}
endif;
