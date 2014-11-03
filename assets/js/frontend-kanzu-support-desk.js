jQuery( document ).ready(function() { 
   /**Toggle display of new ticket form */
   jQuery( "#ksd-new-ticket-frontend-wrap" ).toggle( "slide" ); //Hide it by default
    jQuery( "button#ksd-new-ticket-frontend" ).click(function(e) {//Toggle on button click
        e.preventDefault();
        jQuery( "#ksd-new-ticket-frontend-wrap" ).toggle( "slide" );
    });
  });


 