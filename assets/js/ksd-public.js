jQuery( document ).ready(function() { 
   /**Toggle display of new ticket form on click of the Support button*/   
   if( jQuery("button#ksd-new-ticket-public").length ){//Check if the button exists.        
        jQuery( "button#ksd-new-ticket-public" ).click(function(e) {//Toggle on button click
            e.preventDefault();            
            jQuery( ".ksd-form-hidden-tab" ).toggle( "slide" ).removeClass( 'hidden' );
    });
   };
   //Unhide registration forms added by shortcodes
   if( jQuery("div.ksd-form-short-code").length ){//Check if the button exists.      
       jQuery("div.ksd-form-short-code").removeClass( 'hidden' );//Unhide the form
       jQuery('div.ksd-form-short-code div.ksd-close-form-wrapper').remove();
       //Hide the loading image and response field by default
       jQuery('div.ksd-form-short-code-form-response').hide();
       jQuery('img.ksd_loading_dialog').hide();
   }
   //Hide loading dialog
    if( jQuery("form.ksd-new-ticket-public").length || jQuery("form.ksd-register-public").length ){
        jQuery('img.ksd_loading_dialog').hide();
    }  
    
    /**
     * Ensure that the Google reCAPTCHA checkbox was checked      * 
     * Always returns true unless the form has a reCAPTCHA field. 
     * If ksd_grecaptcha exists, then the form has a reCAPTCHA field
     * @returns {boolean}
     */
    ksdIsGoogleReCaptchaValid = function(){
        if ( 'undefined' !== typeof(ksd_grecaptcha) ){   
            var grecaptchaFormId    = jQuery( 'div.ksd-support-form-submitted' ).find('.g-recaptcha').attr('id');
            var grecaptchaResponse  = '';
            try{
                grecaptchaResponse = grecaptcha.getResponse(grecaptchaWidgetIds[grecaptchaFormId]);
                if ( grecaptchaResponse === "" || grecaptchaResponse === undefined || grecaptchaResponse.length === 0 || ! grecaptchaResponse ){
                    jQuery( "div.ksd-support-form-submitted form span.ksd-g-recaptcha-error").html( ksd_public.ksd_public_labels.msg_grecaptcha_error );
                    return false;
                } 
            }catch(err){
                    jQuery( "div.ksd-support-form-submitted form span.ksd-g-recaptcha-error").html( err );
                    return false;
            }

        }      
        return true;
    };
    
    //Explicitly render Google reCAPTCHA forms
    var grecaptchaWidgetIds = [];//Store the widget IDs. Used by ksdIsGoogleReCaptchaValid() to validate response
    ksdRecaptchaCallback = function(){
    jQuery('[id^=g-recaptcha-field-]').each(function () {
           widgetId = grecaptcha.render(this.id, {'sitekey': ksd_grecaptcha.site_key});
           grecaptchaWidgetIds[this.id] = widgetId;
        });
    };
 
    
    /**AJAX: Log new ticket on submission of the new ticket form**/
    logNewTicket    = function( form ){
        var targetFormDiv = 'div.ksd-support-form-submitted';//Make sure the following actions are on the correct form
        var targetFormClass = targetFormDiv+ ' form';

        jQuery( targetFormClass+' img.ksd_loading_dialog' ).show();//Show the loading button
        jQuery( targetFormClass+' :submit').hide(); //Hide the submit button

        jQuery.post( ksd_public.ajax_url,
                jQuery(form).serialize(), //The action and nonce are hidden fields in the form
                function (response) {
                    jQuery(targetFormClass + ' img.ksd_loading_dialog').hide(); //Hide the loading button
                    var respObj = {};
                    try {
                        //to reduce cost of recalling parse
                        respObj = JSON.parse(response);
                    } catch (err) {
                        jQuery(targetFormDiv + ' div.ksd-support-form-response').show().html(ksd_public.ksd_public_labels.msg_error_refresh);
                        jQuery(targetFormClass + ' :submit').show(); //Hide the submit button
                        return;
                    }
                    //Show the response received. Check for errors
                    if ('undefined' !== typeof (respObj.error)) { 
                        jQuery(targetFormDiv + ' div.ksd-support-form-response').show().html(respObj.error.message);
                        jQuery(targetFormClass + ' :submit').show(); //Show the submit button
                        return;
                    }
                    jQuery(targetFormDiv + ' div.ksd-support-form-response').show().html(respObj);
                    if (jQuery(form).hasClass('.ksd-register-public')) {//Registration successful. Redirect...
                        window.location.replace(ksd_public.ksd_submit_tickets_url);
                    }
                    //Remove the form
                    jQuery(targetFormClass).remove();
                    //Remove the 'submitted' class
                    jQuery(targetFormDiv).removeClass('ksd-support-form-submitted');
                });

    }; 

    jQuery( '.ksd-new-ticket-public input.ksd-submit,.ksd-register-public input.ksd-submit' ).click( function( e ){
        var supportForm    = jQuery( this ).parents( 'form' );                 
        jQuery( supportForm ).parent().addClass( 'ksd-support-form-submitted' );//Tag the submitted form
        if( ! jQuery( supportForm ).valid() || ! ksdIsGoogleReCaptchaValid() ){
           e.preventDefault(); 
           return;
        }        
        
        e.preventDefault(); 
        logNewTicket( supportForm ); 
  
    });
    
    /**
     * Add logic to handle WP uploads (attachments) in the
     * support forms 
     * @since 2.2.4
     */
    if( jQuery( '.ksd-new-ticket-public input.ksd-submit').length ){
        var frame,attachmentHTML;
        jQuery('#ksd-insert-media-button').click(function ( e ) {
            e.preventDefault();
            var supportForm    = jQuery( this ).parents( 'form' );                 
            jQuery( supportForm ).parent().addClass( 'ksd-support-form-attaching' );//Tag the form being used           
            // If the media frame already exists, reopen it.
            if ( frame ) {
              frame.open();
              return;
            }

            // Create a new media frame
            frame = wp.media({
              multiple: true   
            });


            // When an image is selected in the media frame...
            frame.on( 'select', function() {

              // Get media attachment details from the frame state
              var attachments = frame.state().get('selection');
              attachments.map( function( attachment ) {
                attachment = attachment.toJSON();
                 
                // Send the attachment URL to our list
                attachmentHTML = '<li><a href="'+attachment.url+'">'+attachment.title+'</a><input type="hidden" name="ksd_tkt_attachment_ids[]" value="'+attachment.id+'"/><span class="ksd-attachment-remove">x</span></li>';
                jQuery( "div.ksd-support-form-attaching ul.ksd_attachments" ).append( attachmentHTML );             
              });

            });

            // Finally, open the modal on click
            frame.open();
        });

        //Remove an attachment
        jQuery( 'ul.ksd_attachments' ).on( 'click','span.ksd-attachment-remove', function(){
            jQuery( this ).parent().remove();
        });
    };
    
     /**In the front end forms, we use labels in the input fields to
        indicate what info each input requires. On click though, these labels
        need to disappear so the user can type. This function handles the toggling
        of the label's value from a phrase to empty and back as the user focuses/stops focussing 
        on an input **/
      _togglePublicFormFieldValues = function(){          
            //Toggles the form label's value
            function toggleFieldLabelText ( event ){
                     if(jQuery(this).val() === event.data.oldValue){
                        jQuery(this).val(event.data.newValue);                              
                    }                    
            };
            //The fields and their respective default label text
            var newFormFields = { 
                "ksd_cust_fullname" :   ksd_public.ksd_public_labels.lbl_name,               
                "ksd_tkt_subject"   :   ksd_public.ksd_public_labels.lbl_subject,
                "ksd_cust_email"    :   ksd_public.ksd_public_labels.lbl_email,
                "ksd_cust_firstname":   ksd_public.ksd_public_labels.lbl_first_name,            
                "ksd_cust_lastname" :   ksd_public.ksd_public_labels.lbl_last_name,           
                "ksd_cust_username" :   ksd_public.ksd_public_labels.lbl_username            
            };
            //Attach events to the fields 
            jQuery.each( newFormFields, function( fieldName, formValue ) {
                jQuery( 'input[name='+fieldName+']' ).on('focus',{
                                                    oldValue: formValue,
                                                    newValue: "",
                                                    fieldName: fieldName
                                                 }, toggleFieldLabelText);
                jQuery( 'input[name='+fieldName+']' ).on('blur',{
                                                    oldValue: "",
                                                    newValue: formValue,
                                                    fieldName: fieldName
                                               }, toggleFieldLabelText);
            });
        };
    _togglePublicFormFieldValues();
     
    //Close the support tab if the close button is clicked
    jQuery ( '.ksd-new-ticket-form-wrap span.ksd_close_button,.ksd-register-form-wrap span.ksd_close_button' ).click(function(){
         jQuery( ".ksd-form-hidden-tab" ).toggle( "slide" );
    });
    //Toggle 'Show password'
    jQuery('li.ksd-show-password input[name=ksd_cust_show_password]').click( function(){
        if ( jQuery(this).is(':checked')) {
            jQuery( 'input[name=ksd_cust_password]' ).attr( 'type', 'text' );
        }
        else{
            jQuery( 'input[name=ksd_cust_password]' ).attr( 'type', 'password' );
        }
    });
    
    //In single ticket view, send a ticket reply
    if( jQuery( '#wp-ksd-public-new-reply-wrap' ).length ){
        jQuery( '#ksd-public-reply-submit' ).click(function( e ){  
            e.preventDefault();
            if ( ! jQuery( 'form#ksd-reply' ).valid() ){
                return;                
            }
            jQuery('.ksd-public-spinner').addClass('is-active').removeClass('hidden');
            var ticketReply;
            if ( 'undefined' !== typeof(tinyMCE) ){
                tinyMCE.triggerSave(); //Required for the tinyMCE.activeEditor.getContent() below to work
                ticketReply = tinyMCE.activeEditor.getContent();
            }
            else{//In case, for one reason or another, tinyMCE doesn't load
                ticketReply = jQuery('textarea[name=ksd-public-new-reply]').val();                
            }
            var customerEmail = '';
            if ( jQuery( 'input[name=ksd_cust_email]' ).length ){
                customerEmail = jQuery( 'input[name=ksd_cust_email]' ).val();
            }
            jQuery.post(    
                    ksd_public.ajax_url,
                    {   action: 'ksd_reply_ticket',
                        ksd_new_reply_nonce: jQuery('input[name=ksd_new_reply_nonce]').val(), 
                        ksd_ticket_reply: ticketReply,
                        ksd_cust_email: customerEmail,
                        ksd_public_reply_form: jQuery('input[name=ksd_public_reply_form]').val(), 
                        ksd_reply_title: jQuery('h1.entry-title').text(),
                        tkt_id: jQuery('ul#ksd-ticket-replies').attr("class").replace("ticket-","")
                    },
                    function ( response ) {
                        var respObj = {};
                        //To catch cases when the ajax response is not json
                        try {
                            jQuery('.ksd-public-spinner').removeClass('is-active').addClass('hidden');
                            //to reduce cost of recalling parse
                            respObj = JSON.parse(response);
                        } catch (err) {
                            jQuery( '#ksd-public-reply-error' ).removeClass('hidden').html( ksd_public.ksd_public_labels.msg_error_refresh ); 
                            return;
                        }

                        //Check for error in request.
                        if ('undefined' !== typeof (respObj.error)) {
                           jQuery( '#ksd-public-reply-error' ).removeClass('hidden').html( respObj.error.message );
                            return;
                        }
       
                        var d = new Date();
                        replyData = "<li class='ticket-reply'>";
                        replyData += "<span class='reply_author'>"+respObj.post_author+"</span>";
                        replyData += '<span class="reply_date">' + d.toLocaleString() + '</span>';
                        replyData += "<div class='reply_message'>";

                        jQuery( '#ksd-public-reply-success' ).removeClass('hidden').html( ksd_public.ksd_public_labels.msg_reply_sent ).delay(3000).fadeOut();
                        
                        //Append the reply
                        replyData += ticketReply;
                        
                        //Clear the reply field
                        if ( 'undefined' !== typeof(tinyMCE) ){
                            tinyMCE.activeEditor.setContent(''); 
                        }else{
                            jQuery('textarea[name=ksd-public-new-reply]').val('');
                        }
                        
                        replyData += "</div>";
                        replyData += "</li>";
                        jQuery("ul#ksd-ticket-replies").append( replyData );
                    });            
        });
    }
  });
