 <?php
 /**
  * Imports tickets into KSD
  *
  * @package   Kanzu_Support_Desk
  * @author    Kanzu Code <feedback@kanzucode.com>
  * @license   GPL-2.0+
  * @link      http://kanzucode.com
  * @copyright 2014 Kanzu Code
  * @since     1.5.4
  */
  
  class KSD_Importer{  

      public function __construct(){

      }
      
      /**
       * Handle the importation
       */
      public function dispatch ( ) {

         $this->handle_import();

         $this->showform();   
      }

 

         /**
          * Display the upload form.
          * @since 1.5.4
          */
         public  function showform ( ) {
            include_once( KSD_PLUGIN_DIR .  'includes/admin/views/html-admin-tickets-importer.php');      
         }



         /**
          * Parses cvs file and imports the tickets in the appropriate table.
          *
          * @since 1.5.4 
          */

         public function handle_import ( ) {

             if ( ! isset($_POST['ksd-import-submit']) ) return;

             if ( empty( $_FILES ) ) return; //@TODO: Add notice on error             

             $file = $_FILES['ksdimport']['tmp_name'];

             global $current_user;

             $c_id = $current_user->ID;

             $fh = fopen( $file , "r");

             while ( ( $rw = fgetcsv($fh, 9999999, "," ) ) !== FALSE) {
                 //@TODO: Check for format errors and validate some fields

                 $new_ticket                         = new stdClass(); 
                 $new_ticket->tkt_subject            = $rw[0];
                 $new_ticket->tkt_message            = $rw[1];
                 $new_ticket->tkt_channel            = $rw[2];
                 $new_ticket->tkt_status             = $rw[3]; //EMAIL will trigger  an email!!!
                 $new_ticket->tkt_severity           = $rw[4];                
                 $new_ticket->tkt_time_logged        = date_format( new DateTime($rw[5]), 'Y-m-d h:i:s'); 
                 $new_ticket->cust_email             = $rw[6] ;
                 $new_ticket->tkt_updated_by         = $c_id;
                 $new_ticket->tkt_assigned_by        = get_user_by( 'email',   $rw[7] )->ID;
                 $new_ticket->tkt_assigned_to        = get_user_by( 'email',   $rw[8] )->ID;
                 $new_ticket->tkt_time_updated       = date_format( new DateTime($rw[9]), 'Y-m-d h:i:s');
                 $new_ticket->tkt_private_note       = $rw[10];
                 $new_ticket->tkt_logged_by          = $c_id;
                 $new_ticket->tkt_assigned_to        = $c_id;

                 //Log the ticket
                 do_action( 'ksd_log_new_ticket', $new_ticket );
            }

            //@TODO: Add notice when done

         }

  }
