<?php
/**
 * Imports tickets. 
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @since     1.4.0
 */
 
 class KSD_Importer{
     
     public function __construct(){
         
     }
     
     
     public function dispatch ( ) {
                $this->header();

                $this->handle_import();
                
                $this->showform();   

                $this->footer();

     }
     
     /**
      * Prints import page header/title.
      * 
      * @since 1.4.0
      */
     public function header ( ) {
            echo '<div class="wrap">';
            screen_icon();
            echo '<h2>' . __( 'KSD Tickets Importer', 'ksd-importer' ) . '</h2>';
     }
     
        /**
         * Footer
         * 
         * @since 1.4.0 
         */
        public function footer ( ) {
             echo '</div>';
        } 
     
        
        public  function showform(){
            echo '
                 At the moment only csv files are supported. The file  should have the fields below in the same order as they are listed.
                 
                <br /> 
                <ol>
                    <li>T</li>
                    <li>subject</li>
                    <li>message</li>
                    <li>channel=STAFF|FACEBOOK|TWITTER|SUPPORT_TAB|EMAIL|CONTACT_FORM</li>
                    <li>status=NEW|OPEN|ASSIGNED|PENDING|RESOLVED</li>
                    <li>severity=URGENT|HIGH|MEDIUM|LOW</li>
                    <li>time_logged=DD-MM-YYYY HH24:MI:SS</li>
                    <li>customer_email</li>
                    <li>assigned_by=email</li>
                    <li>time_last_updated=DD-MM-YYYY HH24:MI:SS</li>
                    <li>private_note</li>
                </ol>
                 
                <br />
                Sample import
                <div>
                <pre>
T,Ticket subject, Ticket Message,STAFF,NEW,HIGH,21-02-2015 14:00 09:00:00,customer@email.com,admin@yourcompany.com,21-02-2015 14:00 09:00:00,Please update the client on progress every 1hr.
T,Ticket subject, Ticket Message,STAFF,NEW,HIGH,21-02-2015 14:00 09:00:00,customer@email.com,admin@yourcompany.com,21-02-2015 14:00 09:00:00,Please update the client on progress every 1hr.
T,Ticket subject, Ticket Message,STAFF,NEW,HIGH,21-02-2015 14:00 09:00:00,customer@email.com,admin@yourcompany.com,21-02-2015 14:00 09:00:00,Please update the client on progress every 1hr.
                    </pre>
                </div>

                <form>
                <input type="file" name="ksdimport"/> <input type="submit" name="submit" value="Import"/>
                </form>
                 ';
        }
        
        
        /**
         * The main controller for the actual import stage.
         *
         * @param string $file Path to file to import
         * @since 1.4.0 
         */
        function handle_import ( ) {

            if ( ! isset($_POST) ) return;
            
            $file = wp_import_handle_upload();
             
            if ( isset( $file['error'] ) ) {
                echo '<p><strong>' . __( 'Sorry, there has been an error.', 'kanzu-support-desk' ) . '</strong><br />';
                echo esc_html( $file['error'] ) . '</p>';
                return false;
            } else if ( ! file_exists( $file['file'] ) ) {
                echo '<p><strong>' . __( 'Sorry, there has been an error.', 'kanzu-support-desk' ) . '</strong><br />';
                printf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'wordpress-importer' ), esc_html( $file['file'] ) );
                echo '</p>';
                return false;
            }
            
            
            $columns = array(
                'marker',
                'subject',
                'message',
                'channel',
                'status',
                'severity',
                'time_logged',
                'customer_email',
                'assigned_by',
                'time_updated',
                'updated_by',
                'private_note'
            );
            
           //
           include_once( KSD_PLUGIN_DIR.  "includes/libraries/File_CSV_DataSource/DataSource.php");  
           $csv = new File_CSV_DataSource;
           $csv->load( $file );

           var_dump( $csv->connect( $columns ));
           /*
            foreach ( $csv->connect( $columns ) as $csv_data ) {
                //test do_action again.
            }
            * 
            */         
        }

 }
 
 
 ?>