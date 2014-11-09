jQuery( document ).ready(function() { 
   /**Toggle display of new ticket form on click of the Support button*/
   jQuery( "#ksd-new-ticket-frontend-wrap" ).toggle( "slide" ); //Hide it by default
    jQuery( "button#ksd-new-ticket-frontend" ).click(function(e) {//Toggle on button click
        e.preventDefault();
        jQuery( "#ksd-new-ticket-frontend-wrap" ).toggle( "slide" );
    });
    
    /**AJAX: Log new ticket on submission of the new ticket form**/
    logNewTicket    = function(form){
        jQuery( 'img.ksd_loading_button' ).show();//Show the loading button
        jQuery('form.ksd-new-ticket-frontend :submit').hide(); //Hide the submit button
        jQuery.post(    ksd_frontend.ajax_url, 
                        jQuery(form).serialize(), //The action and nonce are hidden fields in the form
                        function( response ) {//@TODO Check for errors 
                            jQuery( 'img.ksd_loading_button' ).hide();//Hide the loading button
                            //Show the response received
                            jQuery ( 'div.ksd-new-ticket-response').show().text(JSON.parse(response));
                            //Remove the form
                            jQuery( 'form.ksd-new-ticket-frontend' ).remove();
                });
            
        }   
    
    //Add validation to the front-end form
    //@TODO Add CAPTCHA
    jQuery("form.ksd-new-ticket-frontend").validate({
        submitHandler: function(form) {
        logNewTicket(form);
        }
        });
    
     /**In the front end forms, we use labels in the input fields to
        indicate what info each input requires. On click though, these labels
        need to disappear so the user can type. This function handles the toggling
        of the label's value from a phrase to empty and back as the user focuses/stops focussing 
        on an input **/
      _toggleFrontEndFormFieldValues = function(){          
            //Toggles the form label's value
            function toggleFieldLabelText ( event ){
                     if(jQuery(this).val() === event.data.oldValue){
                        jQuery(this).val(event.data.newValue);                              
                    }                    
            };
            //The fields and their respective default label text
            var newFormFields = {
                "ksd_cust_fullname" :   "Name",               
                "ksd_tkt_subject" :     "Subject",
                "ksd_cust_email" :      "Email"               
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
            //Handle the textarea too
            jQuery( "textarea[name=ksd_tkt_message]" ).on('focus', function() {
                jQuery( this ).val('');
            });
        }
    _toggleFrontEndFormFieldValues();
     
    //Close the support tab if the close button is clicked
    jQuery ( '#ksd-new-ticket-frontend-wrap img.ksd_close_button' ).click(function(){
         jQuery( "#ksd-new-ticket-frontend-wrap" ).toggle( "slide" );
    });
    
  });


 