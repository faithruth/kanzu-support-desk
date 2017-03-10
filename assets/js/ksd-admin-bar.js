(function($){
    $(document).ready(function(){
        //Count down KSD Admin Bar Menu notifications when menu item clicked
        $('#wp-admin-bar-ksd-admin-bar li').click(function(){
            $.post(ksd_admin_bar.ajax_url, {action: 'ksd_admin_bar_clicked', node_id: $(this).attr('id') }, function(response){
                var respObj = JSON.parse(response)
                if( 'undefined' !== typeof(respObj.message) ){
                    var numNotifications = parseInt($('.ksd-admin-bar-notice').html());
                    numNotifications--;
                    if( numNotifications === 0 ){
                        $('.ksd-admin-bar-notice').remove();
                    }
                    else{
                        $('.ksd-admin-bar-notice').html(numNotifications);
                    }
                }
            });
        });
    });
})(jQuery)