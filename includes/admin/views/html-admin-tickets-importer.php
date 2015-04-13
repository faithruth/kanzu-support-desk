<div class="wrap">
    <div id="icon-tools" class="icon32"><br /></div>
    <h2><?php _e( 'KSD Tickets Importer', 'ksd-importer' ) ; ?></h2>
    <p><?php _e( "Currently, only csv files are supported. The csv file  should have the fields below in the same order they are listed in.","kanzu-support-desk" ); ?></p>
 <?php 
      $ksd_importer_fields = array();  
      $ksd_importer_fields['subject']['values'] = "The ticket subject";                                              
      $ksd_importer_fields['subject']['default'] =   "N/A";                                                
      $ksd_importer_fields['subject']['mandatory'] =  "Yes";
      $ksd_importer_fields['message']['values'] = "The ticket message";                                              
      $ksd_importer_fields['message']['default'] =   "N/A";                                                
      $ksd_importer_fields['message']['mandatory'] =  "Yes";
      $ksd_importer_fields['customer_name']['values'] = "John Doe";                                              
      $ksd_importer_fields['customer_name']['default'] =   "None";                                                
      $ksd_importer_fields['customer_name']['mandatory'] =  "Yes";
      $ksd_importer_fields['customer_email']['values'] = "customer@email.com";                                              
      $ksd_importer_fields['customer_email']['default'] =   "None";                                                
      $ksd_importer_fields['customer_email']['mandatory'] =  "Yes";  
      $ksd_importer_fields['channel']['values'] = "STAFF,FACEBOOK,TWITTER,SUPPORT_TAB,EMAIL,CONTACT_FORM";                                              
      $ksd_importer_fields['channel']['default'] =   "STAFF";                                                
      $ksd_importer_fields['channel']['mandatory'] =  "No";
      $ksd_importer_fields['status']['values'] = "NEW,OPEN,ASSIGNED,PENDING,RESOLVED";                                              
      $ksd_importer_fields['status']['default'] =   "NEW";                                                
      $ksd_importer_fields['status']['mandatory'] =  "No";
      $ksd_importer_fields['severity']['values'] = "URGENT,HIGH,MEDIUM,LOW";                                              
      $ksd_importer_fields['severity']['default'] =   "LOW";                                                
      $ksd_importer_fields['severity']['mandatory'] =  "No";        
      $ksd_importer_fields['time_logged']['values'] = "DD-MM-YYYY HH24:MI:SS";                                              
      $ksd_importer_fields['time_logged']['default'] =   "Current time";                                                
      $ksd_importer_fields['time_logged']['mandatory'] =  "No";
      $ksd_importer_fields['private_note']['values'] = "Ticket private note";                                              
      $ksd_importer_fields['private_note']['default'] =   "None";                                                
      $ksd_importer_fields['private_note']['mandatory'] =  "No";  
 ?>
    <div class="ksd-importer-fields">
        <div class="ksd-field-row ksd-importer-header">
            <div>Field position</div>
            <div>Field</div>
            <div>Possible values</div>
            <div>Default Value</div>
            <div>Mandatory</div>
	</div>
    <?php $ksd_position=1;foreach ( $ksd_importer_fields as $field => $values ): ?>   
        <div class="ksd-field-row">
            <div><?php echo $ksd_position; ?></div>
            <div><?php echo $field; ?></div>
            <div><?php echo $values['values']; ?></div>
            <div><?php echo $values['default']; ?></div>
            <div><?php echo $values['mandatory']; ?></div>
	</div>
        <?php $ksd_position++; ?>
    <?php  endforeach; ?>    
    </div>
    <form action="?import=ksdimporter" method="post"enctype="multipart/form-data" class="ksd-importer-form">
        <label for="ksdimport">Select file to import</label>
        <input type="file" size="30" name="ksdimport" />
         <?php wp_nonce_field( 'ksd-ticket-importer', 'ksd-ticket-import-nonce' ); ?>
        <input class="button-small button button-primary ksd-button" type="submit" name="ksd-import-submit" value="Import Tickets" />
    </form>
    <p>
     Sample file input:
        <pre>
    Ticket subject, This is the ticket message, John Doe, customer@email.com, STAFF, NEW, HIGH,21-02-2015 14:00 09:00:00,Please update the client on progress every 1hr.
    Ticket subject, Ticket message, Jonathan Doe, customer@email.com
    Ticket subject, Ticket message, Jonathan Doe, customer@email.com,,,MEDIUM
    Ticket subject, Ticket message, Jane Doe, the.customer@email.com, EMAIL, OPEN, URGENT,21-02-2015 14:00 09:00:00,Please update the client on progress every 1hr.
        </pre>
    </p>
    <p>NB: Non-mandatory fields can be left blank like in lines 2 and 3 in the sample above</p>
</div>