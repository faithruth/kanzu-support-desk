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
    });
});

