<div class="wrap">
    <div id="icon-tools" class="icon32"><br /></div>
    <h2><?php _e( 'KSD Tickets Importer', 'ksd-importer' ) ; ?></h2>
    <p><?php _e( "Currently, only csv files are supported. The file  should have the fields below in the same order they are listed in.","kanzu-support-desk" ); ?></p>
    <ol class="ksd-importer-fields">
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
    <p>
     Sample file input:
        <pre>
         Ticket subject, Ticket Message,STAFF,NEW,HIGH,21-02-2015 14:00 09:00:00,customer@email.com,admin@yourcompany.com,21-02-2015 14:00 09:00:00,Please update the client on progress every 1hr.
         Ticket subject, Ticket Message,SUPPORT_TAB,NEW,HIGH,21-02-2015 14:00 09:00:00,customer@email.com,admin@yourcompany.com,21-02-2015 14:00 09:00:00,Please update the client on progress every 1hr.
         Ticket subject, Ticket Message,EMAIL,NEW,HIGH,21-02-2015 14:00 09:00:00,customer@email.com,admin@yourcompany.com,21-02-2015 14:00 09:00:00,Please update the client on progress every 1hr.
        </pre>
    </p>
    <form action="?import=ksdimporter" method="post"enctype="multipart/form-data" >
        <label for="ksdimport">Select file to import</label>
        <input type="file" size="30" name="ksdimport" />
         <?php wp_nonce_field( 'ksd-ticket-importer', 'ksd-ticket-import-nonce' ); ?>
        <input class="button-small button button-primary ksd-button" type="submit" name="ksd-import-submit" value="Import Tickets" />
    </form>
</div>