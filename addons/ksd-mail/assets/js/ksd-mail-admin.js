jQuery( document ).ready(function() {
    //Activate/Deactivate license button
    jQuery( 'input[name=ksd_mail_license_status]' ).click( function(){
        //Add a 'Loading button' next to the clicked button
        var targetLicenseStatusSpan = jQuery(this).parents('div.setting').find('span.license_status');
        targetLicenseStatusSpan.html('');
        targetLicenseStatusSpan.addClass('loading');
        var licenseAction;
        if ( jQuery(this).hasClass('ksd-activate_license')){
            licenseAction  = 'activate_license';
        }
        else{
            licenseAction  = 'deactivate_license';
        }
        //Send the request. The variables are from the Kanzu Support Desk Js localization
         jQuery.post(    ksd_admin.ajax_url, 
                             { 	action : 'ksd_modify_license',
                                ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
                                license_action : licenseAction,
                                license : jQuery('input[name=ksd_mail_license_key]').val()
                             }, 
                         function( response ) {
                             targetLicenseStatusSpan.removeClass('loading');
                             targetLicenseStatusSpan.html( JSON.parse(response ));
                         }
                    );
    });
    /**
     * Monitor any change in the mail settings. We do this so that if they are
     * changed, we initiate a new connection to the mail server
     * and if it fails, inform the user immediately
     */
    mailSettingsChanged = function(){
       jQuery("input[name=ksd_mail_settings_changed]").val("yes");
    }
    //Add an event to all mail settings to monitor for changes
    jQuery( "input[name^='ksd_mail_']" ).change(function() { 
       mailSettingsChanged();
    });
    
    //Change mail port depending on user's settings 
    changeMailPort = function( protocol, useSSL ){
        var defaultPort = 110;
        if ( protocol === 'IMAP' && useSSL ){
            defaultPort = 993;
        }
        if ( protocol === 'IMAP' && !useSSL ){
            defaultPort = 143;
        }
        if ( protocol === 'POP3' && useSSL ){
            defaultPort = 995;
        }
        jQuery("input[name=ksd_mail_port]").val(defaultPort);
    };
    //Attach events to change in useSSL and protocol settings
    jQuery( "input[name=ksd_mail_useSSL]" ).on( "click", function(){
        changeMailPort(jQuery( "select[name=ksd_mail_protocol] option:selected" ).text(),jQuery( "input[name=ksd_mail_useSSL]:checked" ).length);
    });
    jQuery( "select[name=ksd_mail_protocol]" ).change(function() {
        changeMailPort(jQuery( "select[name=ksd_mail_protocol] option:selected" ).text(),jQuery( "input[name=ksd_mail_useSSL]:checked" ).length);
        mailSettingsChanged();//The event monitoring changes to mail settings was attached to input fields. This is a select so we attach it too
    });


    //Test mail connection settings
    jQuery( "input[name='ksd_mail_test_connection']" ).click(function() {
         jQuery('#ksd_mail_test_connection').html('Checking...');
         
         jQuery.post(    ksd_admin.ajax_url, 
                             {  action              : 'ksd_mail_test_connection',
                                ksd_mail_connection_nonce     : ksd_admin.ksd_admin_nonce,
                                ksd_mail_server     : jQuery('input[name=ksd_mail_server]').val(),
                                ksd_mail_account    : jQuery('input[name=ksd_mail_account]').val(),
                                ksd_mail_password   : jQuery('input[name=ksd_mail_password]').val(),
                                ksd_mail_protocol   : jQuery('select[name=ksd_mail_protocol]').val(),
                                ksd_mail_port       : jQuery('input[name=ksd_mail_port]').val(),
                                ksd_mail_mailbox    : jQuery('input[name=ksd_mail_mailbox]').val(),
                                ksd_mail_validate_certificate : jQuery('input[name=ksd_mail_validate_certificate]').val(),
                                ksd_mail_useSSL     : jQuery('input[name=ksd_mail_useSSL]').val(),
                             }, 
                         function( response ) {
                             jQuery('#ksd_mail_test_connection').html(response );
                         }
                    );
                    
    });

});

