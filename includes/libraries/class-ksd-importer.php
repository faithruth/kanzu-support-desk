 <?php
 /**
  * Imports tickets. 
  *
  * @package   Kanzu_Support_Desk
  * @author    Kanzu Code <feedback@kanzucode.com>
  * @license   GPL-2.0+
  * @link      http://kanzucode.com
  * @copyright 2014 Kanzu Code
  * @since     1.5.2
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

         /**
          * Display the upload form.
          * @since 1.4.0
          */
         public  function showform ( ) {
             echo '
                  At the moment only csv files are supported. The file  should have the fields below in the same order as they are listed.
                 <br /> 
                 <ol>
                     <li>subject</li>
                     <li>message</li>
                     <li>channel=STAFF|FACEBOOK|TWITTER|SUPPORT_TAB|EMAIL|CONTACT_FORM</li>
                     <li>status=NEW|OPEN|ASSIGNED|PENDING|RESOLVED</li>
                     <li>severity=URGENT|HIGH|MEDIUM|LOW</li>
                     <li>time_logged=DD-MM-YYYY HH24:MI:SS</li>
                     <li>customer_email</li>
                     <li>assigned_by=email</li>
                     <li>assigned_to=email</li>
                     <li>time_last_updated=DD-MM-YYYY HH24:MI:SS</li>
                     <li>private_note</li>
                 </ol>

                 <br />
                 Sample import
                 <div>
                 <pre>
 Ticket subject, Ticket Message,STAFF,NEW,HIGH,21-02-2015 14:00 09:00:00,customer@email.com,admin@yourcompany.com,21-02-2015 14:00 09:00:00,Please update the client on progress every 1hr.

 Ticket subject, Ticket Message,STAFF,NEW,HIGH,21-02-2015 14:00 09:00:00,customer@email.com,admin@yourcompany.com,21-02-2015 14:00 09:00:00,Please update the client on progress every 1hr.

 Ticket subject, Ticket Message,STAFF,NEW,HIGH,21-02-2015 14:00 09:00:00,customer@email.com,admin@yourcompany.com,21-02-2015 14:00 09:00:00,Please update the client on progress every 1hr.
                     </pre>
                 </div>

                 <form action="?import=ksdimporter" method="post"enctype="multipart/form-data" >
                 <div>
                    <div class="">
                        <label for="ksd_mail_server">Select file to import </label>
                        <input type="file" size="30" name="ksdimport" />
                    </div>

                    <div class="">
                        <input class="button-small button button-primary ksd-button" type="submit" name="submit" value="Import" />
                    </div> 

                 </div>   
                 </form>
                  ';
         }

         

         

         /**
          * Parses cvs file and imports the tickets in the appropriate table.
          *
          * @since 1.4.0 
          */

         function handle_import ( ) {

             if ( ! isset($_POST['submit']) ) return;

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

  

  

  ?>

