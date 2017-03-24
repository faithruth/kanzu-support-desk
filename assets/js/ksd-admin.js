/*Load google chart first. */
if ( 'undefined' !== typeof (google) ) {
    google.load("visualization", "1", {packages: ["corechart"]});
}

var ksd_hooks = {
    "ksd_settings_updated": []
}
var KSDHooks = KSDHooks || {};

(function($) {
    $(document).ready(function () {
        //KSD Notifications
        $( "#ksd-notifications" ).slideToggle( "slow" );

        //Added to remove/hide distortion of UI that shows up during initial load of the plugin.
        $("#admin-kanzu-support-desk").css({visibility: 'visible'});

        /**For the general navigation tabs**/
        $("#tabs").tabs().addClass("ui-tabs-vertical ui-helper-clearfix");
        $("#tabs > ul > li").removeClass("ui-corner-top").addClass("ui-corner-left");

        /*Get URL parameters*/
        $.urlParam = function (name) {
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
            if (results === null) {
                return null;
            }
            else {
                return results[1] || 0;
            }
        };


        /*
         * Jquery plugin enhancements 
         * //@TODO cc appears also on Ticket reply forms. Check how it performs on all forms
         */
        //Validation Rule for CC field
        $.validator.addMethod("ccRule", function(value, element, options){
            if( $(element).val() ==ksd_admin.ksd_labels.lbl_CC ) return true;

            emails = $(element).val().split(",");
            cnt    = emails.length;
            for( i = 0; i < cnt; i++){
                _status = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@(?:\S{1,63})$/.test(  emails[i] );
                if( _status === false ){
                    return false;
                }
            }
            return true;

        },ksd_admin.ksd_labels.validator_cc );

        //$ validator messages internationalization
        $.validator.messages.required  = ksd_admin.ksd_labels.validator_required;
        $.validator.messages.email     = ksd_admin.ksd_labels.validator_email;
        $.validator.messages.minlength = $.validator.format( ksd_admin.ksd_labels.validator_minlength );

        //Change default error placement ( errorPlacement ) for jquery form validator
        //TODO: $ error " TypeError: e[d].call is not a function" during validation.
        $.validator.setDefaults({
          errorPlacement: function(error, element){
            $(element).css(  "border-color", "red" );

             $(element).attr('title', $(error).html());
            var tooltips = $(element).tooltip({
              position: {
                my: "top bottom-10",
                at: "left+120 top"
              }
            });
            tooltips.tooltip( "open" );
          },
          success: function(label, element){
              $(element).css(  "border-color", "" );
              $(element).tooltip( "destroy" );
          },
          onfocusout: true,
          ignoreTitle: true
        });




        /*---------------------------------------------------------------*/
        /***************************UTILITIES: Used by all the rest*******/
        /*---------------------------------------------------------------*/
        KSDUtils = function () {
            _this = this;
        };

        KSDUtils.showDialog = function (dialog_type, message) {
            /**Show update/error/Loading dialog while performing AJAX calls and on completion*/
            message = message || ksd_admin.ksd_labels.msg_loading;//Set default message
            //First hide all other dialogs
            $('.ksd-dialog').hide();
            $('.' + dialog_type).html(message);//Set the message
            $('.' + dialog_type).fadeIn(400).delay(3000).fadeOut(400); //fade out after 3 seconds
        };

        KSDUtils.ajaxResponseErrorCheck = function (ajaxResponse) {
            //To catch cases when the ajax response is not json
            try {
                //to reduce cost of recalling parse
                respObj = JSON.parse(ajaxResponse);
            } catch (err) {
                this.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                return true;
            }
            //Check for error in request.
            if ('undefined' !== typeof (respObj.error)) {
                this.showDialog("error", respObj.error.message);
                return true;
            }
            return false;
        };

        KSDUtils.isNumber = function () {
            return typeof n === "number" && isFinite(n) && n % 1 === 0;
        };

        /**
         * Capitalize the first letter in a string
         * @param {string} theString String to capitalize e.g. hello or HELLO
         * @returns string capitalizedString e.g. Hello (all other letters are switched to lowercase, the first to uppercase
         */
        KSDUtils.capitalizeFirstLetter = function (theString) {
            return theString.toLowerCase().replace(/\b[a-z]/g, function (letter) {
                return letter.toUpperCase();
            });
        };

        /**
         * Convert text to a slug. 
         * Replaces all spaces with hyphens, removes anything not alphanumeric, underscore, or hyphen
         * @param {String} Text
         * @returns {String}
         */
        KSDUtils.slugify = function (Text) {
            return Text
                .toLowerCase()
                .replace(/ /g,'-')
                .replace(/[^\w-]+/g,'')
                ;
        };


        /*---------------------------------------------------------------*/
        /***************************JS HOOKS: Callback functions defined by addons *******/
        /*---------------------------------------------------------------*/
        KSDHooks.add = function(hookName, callback){
            ksd_hooks[hookName].push(callback);
        }

        KSDHooks.execute = function(hookName, status, data ){
            for ( f in ksd_hooks[hookName]) {
                try{
                ksd_hooks[hookName][f](status,data);
                    }catch(ex){
                        //Do something
                        //console.log(ex)
                    }
            }
        }

        /*---------------------------------------------------------------*/
        /****************************SETTINGS****************************/
        /*---------------------------------------------------------------*/
        KSDSettings = function () {
            _this = this;
            this.init = function () {            
                this.submitSettingsForm();//Submit the settings            
                this.toggleViewsToHide();//Show/Hide some settings when some checkboxes are checked           
                this.enableAccordion(); //Use an accordion in case we have multiple setting blocks
                this.autocompleteUsers();
                this.changeSubmitBtnVal();
                this.modifyLicense();
                this.enableUsageStats();
                this.notifications();
                this.sendDebugEmail();
                this.resetRoleCapabilities();

            };
        

        /*
         * 
         */
        this.changeSubmitBtnVal = function () {
            $('.ksd-send-email :checkbox').click(function () {
                var $this = $(this);
                var $that = $('[name=ksd-submit-admin-new-ticket]');
                if ($this.is(':checked')) {
                    $that.val('Send')
                } else {
                    $that.val('Save')
                }
            });
        };
        
        /**
         * Add/Remove a role from a user
         * @param {int} userId The user ID affected
         * @param {string} newRole The new role
         * @param {string} change The type of change to make to the user. Can be add_role or remove_role
         * @returns {NA}
         */
        changeUserRole = function( userId, newRole, change, responseElement ){
            $.post(
                    ksd_admin.ajax_url,
                    {   action: 'ksd_change_user_role',
                        user_id: userId,
                        role: newRole,
                        change: change
                    },
            function (response) {
                var respObj = {};
                try {
                    respObj = JSON.parse(response);
                     if ('undefined' !== typeof (respObj.error)) {
                        responseElement.addClass('ksd-error').html( respObj.error.message );
                        return;
                     }
                     responseElement.html('Success');//@RODO Internationalize
                } catch (err) {
                    responseElement.addClass('ksd-error').html( ksd_admin.ksd_labels.msg_error_refresh );
                    return;
                }
            });   
        };
        
        this.sendDebugEmail = function(){
            $('#ksd-send-test-email').click(function (e) {
                e.preventDefault();
                var responseElement = $('#ksd-debug-email-response');
                responseElement.html( ksd_admin.ksd_labels.msg_loading );
                $.post(
                        ksd_admin.ajax_url,
                        {
                            action: 'ksd_send_debug_email',
                            email: $('input[name=debug_test_email]').val()
                        },
                        function (response) {                            
                            if ( response.success ) {
                                responseElement.html( response.data ).addClass('ksd-success');
                            } else {
                                responseElement.html( response.data ).addClass('ksd-error');
                            }
                        }
                );
            });
        }
        
        this.resetRoleCapabilities = function(){
            $('#ksd-reset-role-caps').click(function(e){
                e.preventDefault(); 
                var responseElement = $('#ksd-debug-reset-role-caps-response');
                responseElement.html( ksd_admin.ksd_labels.msg_loading );                
                $.post(
                        ksd_admin.ajax_url,
                        {
                            action: 'ksd_reset_role_caps'
                        },
                        function (response) {                            
                            if ( response.success ) {
                                responseElement.html( response.data ).addClass('ksd-success');
                            } else {
                                responseElement.html( response.data ).addClass('ksd-error');
                            }
                        }
                );               
            });
        }
        
        this.autocompleteUsers = function () {
            $( '.ksd-suggest-user' ).each( function(){
                    var $this       = $( this );
                    var assignRole  = $this.hasClass('ksd-agent-list') ? 'agent' : 'supervisor' ;
                    $this.autocomplete({
                            source:    ksd_admin.ajax_url + '?action=ksd_autocomplete_user',
                            delay:     500,
                            minLength: 2,
                            open: function() {
                                    $( this ).addClass( 'open' );
                            },
                            close: function() {
                                    $( this ).removeClass( 'open' );
                            },
                            select: function( event, ui ) {
                                event.preventDefault();
                                var targetItem  =   $( this ).parent().find('.ksd-user-list');
                                var responseElement = targetItem.find('.ksd-user-agent-response');
                                targetItem.append('<li><a tabindex="-1" class="ksd-search-choice-close" href="#" data-ksd-user-id="'+ui.item.ID+'"></a>'+ui.item.value+'</li>');
                                responseElement.removeClass('hidden').html( 'Adding '+ui.item.value+'...' );//@TODO Internationalize
                                changeUserRole( ui.item.ID, assignRole,'add_role', responseElement );
                                $( this ).val('');
                            }                                 
                    });
            });
            
            $('.ksd-user-list').on('click', '.ksd-search-choice-close', function (event) {
                event.preventDefault();
                var targetItem  =   $( this ).parents('.ksd-user-list');
                var responseElement = targetItem.find('.ksd-user-agent-response');
                var assignRole  = targetItem.hasClass('ksd-agent-list') ? 'agent' : 'supervisor' ;
                $( this ).parent().remove();
                responseElement.removeClass('hidden').html( 'Removing...' );//@TODO Internationalize
                changeUserRole( $( this ).data( "ksdUserId" ), assignRole,'remove_role', responseElement );
            });
        };        
        
        __submitNotificationFeedback = function( data ){
            $.post(ksd_admin.ajax_url,
                    data,
            function (response) {
                var respObj = {};
                //To catch cases when the ajax response is not json
                try {
                    respObj = JSON.parse(response);
                } catch (err) {                    
                    return;
                }
                $('#ksd-notifications').html( '<div class="ksd-notifications-response">'+respObj+ '</div>' ).delay(3000).slideToggle( "slow" );
            });            
        };
        
        this.notifications = function () {
            var notificationID = $('#ksd-notifications').data("notificationId");//NOTE: This doesn't work in IE
            $('.ksd-notification-close img').click(function () {
                $( "#ksd-notifications" ).slideToggle( "slow" );
                var data = { action: 'ksd_notifications_user_feedback', notfxn_ID: notificationID, response: 'close' };
                __submitNotificationFeedback( data );   
                //KSDAnalytics.sendEvent( 'Feedback', 'General', 'close-'+notificationID );
            });   
            //Leave me alone!!!!
            $('.ksd-notification-cancel').click(function () {
                var data = { action: 'ksd_notifications_user_feedback', notfxn_ID: notificationID, response: 'no' };
                __submitNotificationFeedback( data );
                //KSDAnalytics.sendEvent( 'Feedback', 'General', 'leave-me-'+notificationID );
            });    
            //Disable all notifications 
            $( 'a.ksd-notifications-disable' ).click(function () {
                var data = { action: 'ksd_notifications_disable' };
                __submitNotificationFeedback( data );
                //KSDAnalytics.sendEvent( 'Feedback', 'General', 'disable-all-'+notificationID );
            });              
            //Quick call
            $('#ksd-notification-quick-call').click(function () {
                var data = { action: 'ksd_notifications_user_feedback', notfxn_ID: notificationID, response: 'yes' };
                __submitNotificationFeedback( data );
                //KSDAnalytics.sendEvent( 'Feedback', 'Quick Call', 'quick_call' );
            });
            //KSD content
           $('#ksd-notification-content-topic').click(function () {
                var ksdTopics = '';
                $('.ksd-content-topics input:checked').each(function(){
                   // KSDAnalytics.sendEvent( 'Feedback', 'KSD Content', $(this).val() );
                    ksdTopics+=$(this).val()+' ';
                });
                var data = { action: 'ksd_notifications_user_feedback', notfxn_ID: notificationID, response: ksdTopics };
                __submitNotificationFeedback( data );
           });       
           //Enable usage 
           $('#ksd-notification-enable-usage').click(function () {
                var data = { action: 'ksd_enable_usage_stats' };
                __submitNotificationFeedback( data );               
           });
           //Leave a review
           $('a.ksd-notification-review').click(function () {
               $( "#ksd-notifications" ).slideToggle( "slow" );
           });
           //One feature...
           $('#ksd-notification-one-feature').click(function () {
                var data = { action: 'ksd_notifications_user_feedback', notfxn_ID: notificationID, response: $('textarea.ksd-notifications-one-feature').val() };
                __submitNotificationFeedback( data );
                //KSDAnalytics.sendEvent( 'Feedback', 'General', 'one_feature' );
           });    
           //NPS 
           $('ul.ksd-nps-score li').click(function () {
               $('ul.ksd-nps-score li').removeClass('active');
               $(this).addClass('active');
           });
           $('#ksd-notification-nps').click(function () {
               if ( ! $('ul.ksd-nps-score li.active').length ){
                   $('div.ksd-notification-nps-error').html( ksd_admin.ksd_labels.lbl_notification_nps_error );
                   return;
               }
                var data = { action: 'ksd_notifications_user_feedback', notfxn_ID: notificationID, response: $('ul.ksd-nps-score li.active').text() };
                __submitNotificationFeedback( data );
           });   
           
           //Campaigns
           $('#wpbody').on( 'click', '.ksd-campaign-ninety-discount-notice button,.ksd-campaign-ninety-discount-notice a', function(){
                var data = { action: 'ksd_notifications_disable_campaign' };
                __submitNotificationFeedback( data );   
           });        

        };        
        
        /**
         * Enable usage & error statistics
         * @returns {NULL}
         */
        this.enableUsageStats = function () {
            $('button.ksd_enable_usage_stats').click(function () {
                $.post(ksd_admin.ajax_url,
                        {   action: 'ksd_enable_usage_stats'
                        },
                function (response) {
                    var respObj = {};
                    //To catch cases when the ajax response is not json
                    try {
                        //to reduce cost of recalling parse
                        respObj = JSON.parse(response);
                    } catch (err) {
                        KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                        return;
                    }
                    KSDUtils.showDialog("success", respObj);
                });
        });            
        };
        


        /*
         * Submit Settings form.
         */
        this.submitSettingsForm = function () {
            /**AJAX: Update settings**/
            $('form#update-settings').submit(function (e) {
                e.preventDefault();
            });

            $('input[name=ksd-settings-reset]').click(function(e){ 
                var data = {action: 'ksd_reset_settings', ksd_admin_nonce: ksd_admin.ksd_admin_nonce}
                KSDUtils.showDialog("loading");
                $.post(ksd_admin.ajax_url,
                        data,
                        function (response) {
                            if (KSDUtils.ajaxResponseErrorCheck(response)) {
                                return;
                            }
                            KSDUtils.showDialog("success", JSON.parse(response));
                });

            });
            
            $('input[name=ksd-settings-submit]').click(function(e){     
                var data = $('form#update-settings').serialize();//The action and nonce are hidden fields in the form
                
                KSDUtils.showDialog("loading");
                $.post(ksd_admin.ajax_url,
                        data,
                        function (response) {
                            if (KSDUtils.ajaxResponseErrorCheck(response)) {
                                KSDHooks.execute('ksd_settings_updated','error',{});
                                return;
                            }
                            KSDUtils.showDialog("success", JSON.parse(response));
                            KSDHooks.execute('ksd_settings_updated','success',{});
                });

            });

            //Add Tooltips for the settings panel
            $(".help_tip").tooltip();
            $("span.ksd-tkt-status a").tooltip();

        }//eof:submitSettingsForm

        /**
         * Hide or show child settings as the value of their parent  
         * setting changes
         */
        this.toggleViewsToHide = function () {
            var parentFieldsToToggle = ['show_support_tab', 'enable_new_tkt_notifxns', 'enable_recaptcha','enable_customer_signup'];
            $.each(parentFieldsToToggle, function (i, field) {
                //Toggle the view on click    
                $('input[name=' + field + ']').click(function () {
                    $("." + field).toggle("slide");
                });
                //Make sure the fields are hidden if the field's not checked
                if (!$('input[name=' + field + ']').is(":checked")) {
                    $("." + field).hide();
                }
            });
        };
        this.enableAccordion = function () {
            //Only use the accordion if more than one section exists
            if ($('div.ksd-settings-accordion h3').length > 1) {
                $('div.ksd-settings-accordion').accordion({
                    collapsible: true,
                    heightStyle: "content"
                });
            }
            else {//Otherwise, remove the label 'General'
                $('div.ksd-settings-accordion h3').remove();
            }

        };
        
        /**
         * Activate/Deactivate plugin licenses
         * @returns {undefined}
         */
        this.modifyLicense = function () {
            //Activate/Deactivate license button. Match all buttons that end with _license_status (Basically all license buttons)
            $("form.ksd-settings input[name$='_license_status']").click(function () {
                var targetLicenseSetting = $(this).parents('div.setting');
                //Add a 'Loading button' next to the clicked button
                var targetLicenseStatusSpan = targetLicenseSetting.find('span.license_status');
                targetLicenseStatusSpan.html('');
                targetLicenseStatusSpan.addClass('loading');
                var licenseAction;
                if ($(this).hasClass('ksd-activate_license')) {
                    licenseAction = 'activate_license';
                }
                else {
                    licenseAction = 'deactivate_license';
                }
                //$plugin_name, $plugin_author, $plugin_options_key, $license_key, $license_status_key
                var pluginName = targetLicenseSetting.find('span.plugin_name').text();
                var pluginAuthorUri = targetLicenseSetting.find('span.plugin_author_uri').text();
                var pluginOptionsKey = targetLicenseSetting.find('span.plugin_options_key').text();
                var licenseKey = targetLicenseSetting.find('input[type=text]').attr('name');
                var licenseStatusKey = targetLicenseSetting.find('input[type=submit]').attr('name');
                var theLicense = targetLicenseSetting.find("input[name$='_license_key']").val();
                //Send the request. The variables are from the Kanzu Support Desk Js localization
                $.post(ksd_admin.ajax_url,
                        {action: 'ksd_modify_license',
                            ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                            license_action: licenseAction,
                            plugin_name: pluginName,
                            plugin_author_uri: pluginAuthorUri,
                            plugin_options_key: pluginOptionsKey,
                            license_key: licenseKey,
                            license_status_key: licenseStatusKey,
                            license: theLicense
                        },
                function (response) {
                    targetLicenseStatusSpan.removeClass('loading');
                    try {
                        var raw_response = JSON.parse(response);
                    } catch (err) {
                        targetLicenseStatusSpan.html(ksd_admin.ksd_labels.msg_error_refresh);
                        return;
                    }
                    targetLicenseStatusSpan.html(raw_response);
                }
                );
            });
        };
        
        
    };//eof:KSDSettings

    /*---------------------------------------------------------------*/
    /*-------------------DASHBOARD----------------------------------*/
    /*---------------------------------------------------------------*/
    KSDDashboard = function () {
        _this = this;

            this.init = function () {
            this.statistics();
            this.charts();
            this.blogNotifications();
        };


        /**
         * 
         * Add click events to the dashboard summaries
         */
        _addClickEventToSummaries = function () {

            //Total Open Tickets
            $("#admin-kanzu-support-desk ul.dashboard-statistics-summary li.open").click(function () {
                window.location.href = "?post_status=all&post_type=ksd_ticket&ksd_statuses_filter=open";                
            });

            //Unassigned Tickets
            $("#admin-kanzu-support-desk ul.dashboard-statistics-summary li.unassigned").click(function () {
                window.location.href = "?post_type=ksd_ticket&ksd_view=unassigned"; 
            });
            
        }
        /*
         * Show statistics summary.
         */
        this.statistics = function () {
            /**AJAX: Retrieve summary statistics for the dashboard**/
            if ($("ul.dashboard-statistics-summary").hasClass("pending")) {
                $.post(ksd_admin.ajax_url,
                        {   action: 'ksd_get_dashboard_summary_stats',
                            ksd_admin_nonce: ksd_admin.ksd_admin_nonce
                        },
                function (response) {
                    $("ul.dashboard-statistics-summary").removeClass("pending");
                    try {
                        var raw_response = JSON.parse(response);
                    } catch (err) {
                        $('ul.dashboard-statistics-summary').html(ksd_admin.ksd_labels.msg_error);
                        return;
                    }
                    if ('undefined' !== typeof (raw_response.error)) {
                        $('ul.dashboard-statistics-summary').html(raw_response.error.message);
                        return;
                    }
                    var unassignedTickets = ('undefined' !== typeof raw_response.unassigned_tickets ? raw_response.unassigned_tickets : 0);
                    var openTickets = ('undefined' !== typeof raw_response.open_tickets ? raw_response.open_tickets : 0)
                    var averageResponseTime = ('undefined' !== typeof raw_response.average_response_time ? raw_response.average_response_time : '00:00:00');
                    var the_summary_stats = "";
                    the_summary_stats += "<li class='open ksd-dash-click'><span>" + ksd_admin.ksd_labels.dashboard_open_tickets + "</span>" + openTickets + "</li>";
                    the_summary_stats += "<li class='unassigned ksd-dash-click'><span>" + ksd_admin.ksd_labels.dashboard_unassigned_tickets + "</span>" + unassignedTickets + "</li>";
                    the_summary_stats += "<li><span>" + ksd_admin.ksd_labels.dashboard_avg_response_time + "</span>" + averageResponseTime + "</li>";
                    $("ul.dashboard-statistics-summary").html(the_summary_stats);

                    //Add click events
                    _addClickEventToSummaries();
                });
            }
        }//eof:statistics

        /*Initialise charts*/
        this.charts = function () {
            try {
                /**The dashboard charts. These have their own onLoad method so they can't be run inside $( document ).ready({});**/
                function ksdDrawDashboardGraph() {
                    if ( 'ksd-dashboard' !== ksd_admin.ksd_current_screen ){
                        return;
                    }
                    $.post(ksd_admin.ajax_url,
                            {   action: 'ksd_dashboard_ticket_volume',
                                ksd_admin_nonce: ksd_admin.ksd_admin_nonce
                            },
                    function ( response ) {
                        var respObj = JSON.parse(response);
                        if ('undefined' !== typeof (respObj.error)) {
                            $('#ksd_dashboard_chart').html(respObj.error.message);
                            return;
                        }
                        var ksdData = google.visualization.arrayToDataTable(respObj);
                        var ksdOptions = {
                            title: ksd_admin.ksd_labels.dashboard_chart_title
                        };
                        var ksdDashboardChart = new google.visualization.LineChart(document.getElementById('ksd_dashboard_chart'));
                        //Add a listener to know when drawing the chart is complete.                     
                        google.visualization.events.addListener(ksdDashboardChart, 'ready', function () {
                            if (!$('ul.ksd-main-nav li:first').hasClass("ui-tabs-active")) {
                                //ksdChartContainer.style.display = 'none'; //If our dashboard tab isn't the selected one, we hide it. 
                            }
                        });
                        ksdDashboardChart.draw(ksdData, ksdOptions);
                    });//eof: $.port
                }
                google.setOnLoadCallback(ksdDrawDashboardGraph);
            } catch (err) {
                $('#ksd_dashboard_chart').html(err);
            }
        };//eof:charts
        this.blogNotifications = function () {
            //Show/Hide the notifications panel
            $('.admin-ksd-title span.more_nav img').click(function (e) {
                e.preventDefault();
                $(this).toggleClass("active");
                $("#ksd-blog-notifications").toggle("slide");
            });
            //Retrieve the blog notifications
            try {
                if ( 'ksd-dashboard' !== ksd_admin.ksd_current_screen && 'ksd-addons' !== ksd_admin.ksd_current_screen && 'ksd-settings' !== ksd_admin.ksd_current_screen ){
                    return;
                }
                $.post(ksd_admin.ajax_url,
                        {   action: 'ksd_get_notifications',
                            ksd_admin_nonce: ksd_admin.ksd_admin_nonce
                        },
                function (response) {
                    var respObj = JSON.parse(response);
                    if ('undefined' !== typeof (respObj.error)) {
                        $('#ksd-blog-notifications').html(respObj.error);
                        return;
                    }
                    //Parse the XML. We chose to do it here, rather than in the PHP (at the server end)
                    //for better performance (no impact on the server)
                    notificationsXML = $.parseXML(respObj);
                    notificationData = '<ul>';
                    $(notificationsXML).find("item").each(function (i, item) {
                        blogPost = $(this);
                        notificationData += '<li>';
                        notificationData += '<a href="' + blogPost.find('link').text() + '" target="_blank" class="post-title">' + blogPost.find('title').text() + '</a>';
                        notificationData += '<span class="date-published">' + blogPost.find('pubDate').text() + '</span>';
                        notificationData += '<a href="' + blogPost.find('link').text() + '" target="_blank" class="excerpt"><p>' + blogPost.find('description').text().substr(0, 100) + '...</p></a>';
                        notificationData += '</li>';
                        return i < 2;//Stops the loop after the first 3 items are returned
                    });
                    notificationData += '</ul>';
                    //Add the entries to the div*/
                    $("#ksd-blog-notifications").html(notificationData);
                });
            } catch (err) {
                $('#ksd-blog-notifications').html(err);
            }
        };//eof:notifications
    };//eof:Dashboard

    /*---------------------------------------------------------------*/
    /*---------------------------HELP-------------------------------*/
    /*-------------------------------------------------------------*/
    KSDHelp = function () {
        _this = this;
        this.init = function () {
            //Submit feedback
            this.submitFeedbackForm();
        };

        /*
         * Submit Feedback form.
         */
        this.submitFeedbackForm = function () {
            /**AJAX: Send Feedback**/
            $('form#ksd-feedback').submit(function (e) {
                e.preventDefault();
                KSDUtils.showDialog("loading", ksd_admin.ksd_labels.msg_sending);
                $.post(ksd_admin.ajax_url,
                        $(this).serialize(), //The action and nonce are hidden fields in the form, 
                        function (response) {
                            if (KSDUtils.ajaxResponseErrorCheck(response)) {
                                return;
                            }
                            KSDUtils.showDialog("success", JSON.parse(response));
                        });
            });
            //All other feedback forms. They start with class ksd-feedback-
            $("form[class^='ksd-feedback-']").submit(function (e) {
                e.preventDefault();
                var form = $(this);
                KSDUtils.showDialog("loading", ksd_admin.ksd_labels.msg_sending);
                $.post(ksd_admin.ajax_url,
                        $(this).serialize(), //The action and nonce are hidden fields in the form, 
                        function (response) {
                            if (KSDUtils.ajaxResponseErrorCheck(response)) {
                                return;
                            }
                            KSDUtils.showDialog("success", JSON.parse(response));
                            form.remove();//Remove the form
                            $('.ksd-feedback-response').html( JSON.parse(response) ).fadeIn(400).delay(2000).fadeOut(400);
                        });
            });
        };
 
    };

    /*---------------------------------------------------------------*/
    /*************************************TICKETS*********************/
    /*---------------------------------------------------------------*/
    KSDTickets = function () {
        _this = this;
        this.init = function () {
            this.addUnreadTicketCount();
            //this.uiTabs();
            this.uiListTickets();
            this.newTicket();
            this.replyTicketForm();

            this.attachDeleteTicketEvent();
            this.attachChangeTicketStatusEvents();
            this.attachAssignToEvents();
            this.attachChangeSeverityEvents();
            this.attachMarkReadUnreadEvents();
            this.uiSingleTicketView();
            this.attachCCFieldEvents();
            
            //Ticket info
            this.ticketInfo();
            
             //Page Refresh
            this.attachRefreshTicketsPage();     
            //Merge tickets
            this.mergeTickets();
            
            this.formatTicketReplies();
            
        };
        
        this.addUnreadTicketCount = function(){
            $('#menu-posts-ksd_ticket > a div.wp-menu-name').append('<span class="unread-display update-plugins count-0"><span class="unread-count"></span></span>');
            $.post(
                    ksd_admin.ajax_url,
                    {
                        action: 'ksd_get_unread_ticket_count'
                    },
                    function (response) {                            
                        if ( response.success ) {
                            $('#menu-posts-ksd_ticket span.unread-display span').html( response.data ).parent().removeClass( 'count-0' ).addClass( 'count-'+response.data );
                        } 
                    }
            );               
 
        };
        
        this.mergeTickets = function(){
            if( $('#ksd-merge-parent-ticket-title').length ){
                $('#ksd-merge-parent-ticket-title').html( $('#titlediv h2.post_title').text() );
            }
            //Show the first 'Merge' dialog
             $('#merge-tickets-button').click(function(e){
                e.preventDefault();                
                $('#ksd-merge-ticket-wrap').dialog({
                    dialogClass: "ksd-merge-no-close",
                    modal: true,
                    buttons: {
                        "Cancel": function () {
                            $(this).dialog("close");
                        }
                    }
                });  
            });
 
           //Get a list of possible tickets to merge
            $('#ksd-merge-ticket-search').click(function(e){
                e.preventDefault();   
                $('.ksd-merge-spinner').removeClass('hidden').addClass('is-active');
                $.post(
                ksd_admin.ajax_url,
                {
                    action: 'ksd_get_merge_tickets',
                    parent_tkt_ID: $('input[name=ksd-merge-parent-ticket]').val(),
                    _ajax_ksd_merging_nonce : $('#_ajax_ksd_merging_nonce').val(),
                    search: $('#ksd-merge-ticket-search-text').val()
                },
                function ( response ) {          
                    $('.ksd-merge-spinner').removeClass('is-active').addClass('hidden');
                    var responseContainer = $('ul.ksd-merge-tickets-list');
                    responseContainer.html(''); 
                    try{
                        respObj = JSON.parse( response );
                        if ( $.isArray( respObj ) && respObj.length > 0 ) {                      
                          $.each( respObj, function ( key, value ) {
                             responseContainer.append('<li data-ksd-merge-tkt-id="'+value.ID+'" class="ksd-merge-do-merge"> #'+value.ID+' '+value.title+'</li>');
                          });                       
                        }else{
                            responseContainer.html('<li>No results found. Please search again</li>');
                        }
                    }catch(err){
                        responseContainer.html('<li>An error occured. Please re-try</li>');
                    }

                }
            );  
            });
            
            //On selecting one of the possible tickets as the merge candidate
             $("#ksd-merge-ticket-wrap").on('click', '.ksd-merge-do-merge', function (event) {
                 event.preventDefault();
                 var mergeID = $(this).data('ksdMergeTktId');
                 $('#ksd-merge-merge-ticket-title').html( $(this).html() );
                 $('#ksd-merge-merge-ticket-id').val( mergeID );
                 $('#ksd-merge-ticket-merge-button').fadeIn();//In case this button's hidden
                 $('.ksd-merge-ticket-merge-wrap').removeClass('hidden');
             });
             
             //The merge is imminent. On selecting to merge. One more step before the merge is sealed..
             $('#ksd-merge-ticket-merge-button').click(function(e){
                e.preventDefault();
                $(this).fadeOut();
                $('#ksd-merge-ticket-select').removeClass('hidden');
             });
             
             //On canceling the merger, boohoo!! 
             $("#ksd-merge-ticket-wrap").on('click', '#ksd-merge-cancel', function (e) {
                e.preventDefault();
                $('#ksd-merge-merge-ticket-title').html( '' );
                $('#ksd-merge-merge-ticket-id').val( 0 );
                $('.ksd-merge-ticket-merge-wrap,#ksd-merge-ticket-select').addClass('hidden');
                $(this).fadeOut();
                $('#ksd-merge-ticket-wrap').dialog("close");
             });             
             
             //On confirming the merger. The deal is sealed!!              
             $('#ksd-merge-ticket-confirm').click(function(e){
                e.preventDefault();
                $('.ksd-merge-spinner').removeClass( 'hidden' ).addClass( 'is-active' );
                $.post(
                ksd_admin.ajax_url,
                {
                    action: 'ksd_merge_tickets',
                    parent_tkt_ID: $('input[name=ksd-merge-parent-ticket]').val(),
                    _ajax_ksd_merging_nonce : $('#_ajax_ksd_merging_nonce').val(),
                    merge_tkt_ID: $('#ksd-merge-merge-ticket-id').val()
                },
                function ( response ) { 
                   $('.ksd-merge-spinner').removeClass( 'is-active' ).addClass( 'hidden' );
                   $('#ksd-merge-final-response').removeClass('empty');
                   var responseContainer = $('#ksd-merge-final-response');
                   if ( response.success ) {
                       responseContainer.html( response.data.message );
                        location.reload();
                    }else{
                         responseContainer.html( response.data.message );
                    }
                });
             });  
        };
        
        /*
         * Total ticket indicator in ticket filters
         */

        _totalTicketsPerFilter = function () {
            if( 'ksd-ticket-list' !== ksd_admin.ksd_current_screen ){
                return;
            }
            var data = {
                action: 'ksd_filter_totals',
                ksd_admin_nonce: ksd_admin.ksd_admin_nonce
            };

            $.post(ksd_admin.ajax_url, data, function (response) {
                var respObj = {};

                //To catch cases when the ajax response is not json
                try {
                    //to reduce cost of recalling parse
                    respObj = JSON.parse(response);
                } catch (err) {
                    KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                    return;
                }

                if ( $.isArray(respObj) ) {
                    $.each(respObj[0], function ( key, value) {
                        $( 'ul.subsubsub li.'+key+' a' ).append( '<span class="count"> ('+value+') </span>' );
                    });
                }

            });
        };
        
        /**
         * To each row in the ticket grid, add a class
         * showing the row's severity
         * @returns {undefined}
         */
        _addSeverityClassToTicketGrid = function(){        
            $("tbody#the-list tr").each(function() {
              $this = $(this);
              var severity = $this.find("td.column-severity").html();
              $this.addClass( severity );
            });
        };

 
        /*
         * List all tickets
         */
        this.uiListTickets = function () {
            //Add counts to custom views
            if ( $ ( 'ul.subsubsub li.mine' ) ){//If we are on the ticket grid and the custom views are shown
                _totalTicketsPerFilter();
                _addSeverityClassToTicketGrid();
            }
            //If we are displaying the ticket list view
            if( $('select[name=_status]').length && 'ksd-ticket-list' === ksd_admin.ksd_current_screen ){
                $('option[value=publish],option[value=pending]').remove();
                $('select[name=_status]').append( ksd_admin.ksd_statuses );
            }
            //Set current active view for our custom views
            if( $.urlParam('ksd_view') ) {
                var currentView = $.urlParam('ksd_view');
                if( 'mine' === currentView || 'unassigned' === currentView ) {
                    $( 'li.'+currentView+' a' ).addClass( 'current' );
                }
            }
        };//eof:
        
        /**
         * Update ticket information
         * @returns {undefined}
         */
        this.ticketInfo = function(){
            //Remove the default WP permalink for tickets with hash URLs
            if( $('#ksd-edit-slug-wrapper').length){
                $('#edit-slug-box').remove();
            }
                
            $('input#ksd-update-ticket-info').click(function(){
                $('#ksd-ticket-info-action span.spinner').addClass('is-active');
                var post_title = $('input[name=post_title]').val();
                if ( undefined === post_title ){
                    post_title = $( '#titlewrap h2.post_title' );
                }
                var data = {
                    action: 'ksd_update_ticket_info',
                    ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                    tkt_id: $.urlParam('post'),
                    ksd_reply_title: post_title, 
                    ksd_tkt_info: $("select[name^='_ksd_tkt_info_']").serialize()
                };
            $.post( ksd_admin.ajax_url, data, function (response) {
                var respObj = {};
                 $('#ksd-ticket-info-action span.spinner').removeClass('is-active');
                //To catch cases when the ajax response is not json
                try {
                    //To reduce cost of recalling parse
                    respObj = JSON.parse(response);
                } catch (err) {
                    KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                    return;
                }
                //Refresh the ticket activity
               _this.getTicketActivity();
            });
            });
            
        }
 

        /**
         * AJAX: Send an AJAX request to re-assign a ticket
         * @param int tkt_id The ticket ID
         * @param int assign_assigned_to The ID of the user to assign the ticket to
         */
        this.reassignTicket = function (tkt_id, assign_assigned_to, singleTicketView) {
            singleTicketView = ( 'undefined' === typeof singleTicketView ? false : true );
            KSDUtils.showDialog("loading");
            $.post(ksd_admin.ajax_url,
                    {action: 'ksd_assign_to',
                        ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                        tkt_id: tkt_id,
                        ksd_current_user_id: ksd_admin.ksd_current_user_id,
                        tkt_assign_assigned_to: assign_assigned_to
                    },
            function (response) {
                var respObj = {};
                //To catch cases when the ajax response is not json
                try {
                    //to reduce cost of recalling parse
                    respObj = JSON.parse(response);
                } catch (err) {
                    KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                    return;
                }

                //Check for error in request.
                if ('undefined' !== typeof (respObj.error)) {
                    KSDUtils.showDialog("error", respObj.error.message);
                    return;
                }
                KSDUtils.showDialog("success", respObj);
                if( !singleTicketView ){
                    _this.ksdRefreshTicketsPage();//Refresh the page
                }
            });

        };

        /**
         * AJAX. Mark a ticket as read/unread
         * @param int tkt_id The ticket's ID
         * @param int markAsRead 1 to mark ticket as read, 0 to mark it as unread
         * @param boolean singleTicketView Whether this is a single ticket view or not. Default is false
         * @returns {undefined}
         */
        this.markTicketReadUnread = function (tkt_id, markAsRead, singleTicketView ) {
            singleTicketView = ( 'undefined' === typeof singleTicketView ? false : true );
            KSDUtils.showDialog("loading");
            $.post(ksd_admin.ajax_url,
                    {action: 'ksd_change_read_status',
                        ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                        tkt_id: tkt_id,
                        tkt_is_read: markAsRead
                    },
            function (response) {
                var respObj = {};
                //To catch cases when the ajax response is not json
                try {
                    //to reduce cost of recalling parse
                    respObj = JSON.parse(response);
                } catch (err) {
                    KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                    return;
                }

                //Check for error in request.
                if ('undefined' !== typeof (respObj.error)) {
                    KSDUtils.showDialog("error", respObj.error.message);
                    return;
                }
                KSDUtils.showDialog("success", respObj);
                if( !singleTicketView ){//If we are in the ticket grid
                    _this.ksdRefreshTicketsPage();//Refresh the page
                }
            });
        };

        /**
         * Change a ticket's severity
         * @param int tkt_id
         * @param string tkt_severity New severity
         */
        this.changeTicketSeverity = function (tkt_id, tkt_severity, singleTicketView ) {
            singleTicketView = ( 'undefined' === typeof singleTicketView ? false : true );
            KSDUtils.showDialog("loading");
            $.post(ksd_admin.ajax_url,
                    {action: 'ksd_change_severity',
                        ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                        tkt_id: tkt_id,
                        tkt_severity: tkt_severity
                    },
            function (response) {
                var respObj = {};
                //To catch cases when the ajax response is not json
                try {
                    //to reduce cost of recalling parse
                    respObj = JSON.parse(response);
                } catch (err) {
                    KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                    return;
                }

                //Check for error in request.
                if ('undefined' !== typeof (respObj.error)) {
                    KSDUtils.showDialog("error", respObj.error.message);
                    return;
                }
                KSDUtils.showDialog("success", respObj);
                 if( !singleTicketView ){
                    _this.ksdRefreshTicketsPage();//Refresh the page
                }
            });

        };

        /**
         * Attach the event that deletes single tickets
         * @returns {undefined}
         */
        this.attachDeleteTicketEvent = function () {
            $("#ticket-tabs").on('click', '.ticket-actions a.trash', function (event) {
                event.preventDefault();
                var tkt_id = $(this).attr('id').replace("tkt_", ""); //Get the ticket ID
                _this.deleteTicket(tkt_id);
            });
        };
        //---------------------------------------------------------------------------------
        /**AJAX: Delete a ticket **/
        this.deleteTicket = function (tkt_id) {
            displayDialog = '#delete-dialog';
            if ($.isArray(tkt_id)) {
                displayDialog += '-bulk';
            }
            $(displayDialog).dialog({
                modal: true,
                buttons: {
                    Yes: function () {
                        $(this).dialog("close");
                        KSDUtils.showDialog("loading");
                        $.post(ksd_admin.ajax_url,
                                {action: 'ksd_delete_ticket',
                                    ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                                    tkt_id: tkt_id
                                },
                        function (response) {
                            var respObj = {};
                            //To catch cases when the ajax response is not json
                            try {
                                //to reduce cost of recalling parse
                                respObj = JSON.parse(response);
                            } catch (err) {
                                KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                                return;
                            }
                            //Check for error in request.
                            if ('undefined' !== typeof (respObj.error)) {
                                KSDUtils.showDialog("error", respObj.error.message);
                                return;
                            }
                            if (!$.isArray(tkt_id)) {//Signle ticket deletion
                                $('.ticket-list div#ksd_tkt_id_' + tkt_id).remove();
                            }
                            else {//Delete tickets in bulk
                                $.each(tkt_id, function (index, the_ID) {
                                    $('.ticket-list div#ksd_tkt_id_' + the_ID).remove();
                                });
                            }
                            KSDUtils.showDialog("success", respObj);
                        });
                    },
                    No: function () {
                        $(this).dialog("close");
                    }
                }
            });
            $("div.ui-widget-overlay").remove();
        };

        //--------------------------------------------------------------------------------------
        /**AJAX: Send a single ticket response when it's been typed and 'Reply' is hit**/
        //Also, update the private note when 'Update Note' is clicked  
        this.replyTicketAndUpdateNote = function () {
            var isTinyMCEActive = false;
            var action = $("input#ksd-reply-ticket-submit").attr("name");//ksd_reply_ticket or ksd_update_private_note
            $('.ksd-reply-spinner').removeClass('hidden').addClass('is-active');
            var post_title = $('input[name=post_title]').val();//Our JS replaces this field with an h2 field. We keep this here just as a fallbback
            if ( 'undefined' === typeof ( post_title ) ){
                post_title = $( '#titlewrap h2.post_title' ).text();//This is the title we expect to get always
            }
            //Validate CC when action is ksd_reply_ticket and indicate that there is validation error
            $('#ksd-cc-field').attr('title','');
            $('#ksd-cc-field').removeClass('ksd-cc-field-error');
            if( $('#ksd-cc-field').val() && 'none' !== $('#ksd-cc-field').css("display") ){
                var ccArr = $('#ksd-cc-field').val().split(',');
                var ccLen = ccArr.length;
                for( i=0; i < ccLen; i++ ){
                    if( ! ccArr[i].match(/@/) ){
                        $('#ksd-cc-field').attr('title', ksd_admin.ksd_labels.validator_cc ); 
                        $('#ksd-cc-field').addClass('ksd-cc-field-error');
                        $('.ksd-reply-spinner').removeClass('is-active').addClass('hidden');
                        return false;
                    }
                }
            }
            var ticketReply = $('textarea[name=ksd_ticket_reply]').val();
            if ( 'undefined' !== typeof( tinyMCE ) && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden() ){
                tinyMCE.triggerSave(); //Required for the tinyMCE.activeEditor.getContent() below to work
                ticketReply = tinyMCE.activeEditor.getContent();
                isTinyMCEActive = true;
            }
            $.post(    ksd_admin.ajax_url,
                    {   action: action,
                        ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                        ksd_ticket_reply: ticketReply,
                        ksd_reply_title: post_title,
                        tkt_private_note: $('textarea[name=tkt_private_note]').val(),
                        tkt_id: $.urlParam('post'),
                        ksd_tkt_cc: $("#ksd-cc-field").val()
                    },
                    function (response) {
                        var respObj = {};
                        //To catch cases when the ajax response is not json
                        try {
                            $('.ksd-reply-spinner').removeClass('is-active').addClass('hidden');
                            //to reduce cost of recalling parse
                            respObj = JSON.parse(response);
                        } catch (err) {
                            KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                            return;
                        }

                        //Check for error in request.
                        if ('undefined' !== typeof (respObj.error)) {
                            KSDUtils.showDialog("error", respObj.error.message);
                            return;
                        }
                        
                        var d = new Date();
                        replyData = "<li class='ticket-reply "+respObj.post_type+"'>";
                        replyData += "<span class='reply_author'>"+respObj.post_author+"</span>";
                        replyData += '<span class="reply_date">' + d.toLocaleString() + '</span>';
                        replyData += "<div class='reply_message'>";

                        if( respObj.rep_cc != null && respObj.rep_cc.match(/@/)){
                            replyData += "<div class='ksd-tkt-cc-wrapper'><span class='ksd_cc'>" + ksd_admin.ksd_labels.lbl_CC + ": "+ respObj.rep_cc + "</span></div>";
                        }
							
                        switch ( action ) {
                            case "ksd_update_private_note":
                                KSDUtils.showDialog("success", respObj);
                                replyData += $('textarea[name=tkt_private_note]').val();//Get the content                          
                                $('textarea[name=tkt_private_note]').val('');//Clear the field
                                break;
                            default:
                                KSDUtils.showDialog( "success", ksd_admin.ksd_labels.msg_reply_sent );
                                replyData += ticketReply;//Get the content 
                                if( isTinyMCEActive ){
                                    tinyMCE.activeEditor.setContent(''); //Clear the reply field
                                }
                                else{
                                    $('textarea[name=ksd_ticket_reply]').val('');
                                }
                        }
                        replyData += "</div>";
                        replyData += "</li>";
                        $("ul#ksd-ticket-replies").append( replyData );
                    });
        };
        
        /**
         * Format a single reply message. Particularly looks out for
         * the tags appended to email messages of the format:
         * "On {DATE}, FirstName LastName <address@domain.com> wrote:". This matches VERY many clients
         * Supports other languages too; currently supports English and German
         * German equivalent:
         * "Den {DATE}, FirstName LastName <address@domain.com> skrev:
         * @returns {string} Formated reply message wrapping tags in div with class ksd_extra
         */
        this.formatSingleReplyMessage = function( replyMessage ){
            replyTag = /(On.*wrote:|Den.*skrev:)/g;//Match all those mentioned above.@TODO Internationalize this
            return replyMessage.replace( replyTag, "<div class='ksd_extra'>$1</div>");
        }

        /**
         * Format ticket replies. Hide extra content from
         * the previous message and generally make the displayed content
         * more user-friendly
         * This builds on what this.formatSingleReplyMessage does
         */
        this.formatTicketReplies = function () {
            /* #1 First match extra content from various email clients and wrap it in class 'ksd_extra'. We match the extra content
             based on knowing that content's structure. Currently matches Gmail (Android and Desktop) & Outlook. To be expanded
             -------------------------------------------------------------------------------------------*/
            //Match Outlook 2013 extra content  @TODO Add mobile outlook, outlook 2007 and 2010
            $('p:contains("-----Original Message-----")').nextUntil("div").addBack().wrapAll('<div class="ksd_extra"></div>');
            //Match Gmail ( Android and Desktop ) clients
            $('div.gmail_quote,blockquote.gmail_quote').addClass('ksd_extra');
            //Match Yahoo desktop clients. Written separately from the rest merely for legibility
            $('div.yahoo_quoted').addClass('ksd_extra');
            //@TODO Add more mail clients, IOS particularly
            
            /* #2 To the content we've wrapped in class 'ksd_extra' in #1 above, append the icon that'll be used to toggle the extra content*/
            $('#ksd-single-ticket .ksd_extra').before('<div class="replies-more" title="' + ksd_admin.ksd_labels.lbl_toggle_trimmed_content + '"></div>');

            // #3 Add an event to that icon we appended
            $('#ksd-single-ticket').on('click', '.replies-more', function () {
                $(this).parents('.ticket-reply').find('.ksd_extra').toggle('slide');//Go up the DOM, find the ticket reply then find the extra content in it
            });

            //#4 Initially, hide all the extra content
            $('.ksd_extra').toggle();
        };

        this.replyTicketForm = function () {
            $("form#edit-ticket").validate({//@TODO Might have to remove this validation altogether
                submitHandler: function (form) {
                    _this.replyTicketAndUpdateNote(form);
                }
            });
            $('input#ksd-reply-ticket-submit').click(function ( e ) {
                e.preventDefault();
                _this.replyTicketAndUpdateNote();
            });    

            /*-------------------------------------------------------------------------------------------------
             * AJAX: Log New ticket
             */
            ksdLogNewTicketAdmin = function (form) {
                KSDUtils.showDialog("loading");//Show a dialog message
                $.post(ksd_admin.ajax_url,
                        $(form).serialize(), //The action and nonce are hidden fields in the form
                        function (response) {
                            var respObj = {};
                            //To catch cases when the ajax response is not json
                            try {
                                //to reduce cost of recalling parse
                                respObj = JSON.parse(response);
                            } catch (err) {
                                KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                                return;
                            }

                            //Check for error in request.
                            if ('undefined' !== typeof (respObj.error)) {
                                KSDUtils.showDialog("error", respObj.error.message);
                                return;
                            }
                            KSDUtils.showDialog("success", respObj);
                            //We send an email to the admin telling them about the new ticket. We do this by AJAX
                            //because our tests showed that wp_mail took in some cases 5 seconds to return a response
                            $.post(ksd_admin.ajax_url,
                                    {   action: 'ksd_notify_new_ticket',
                                        ksd_admin_nonce: ksd_admin.ksd_admin_nonce
                                    },
                            function (response) {
                                //To catch cases when the ajax response is not json
                            try {
                                //to reduce cost of recalling parse
                                respObj = JSON.parse(response);
                            } catch (err) {
                                KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                                return;
                            }
                            //Show notifications sent message
                            KSDUtils.showDialog("success", respObj);
                            //Redirect to the Tickets page
                            window.location.replace(ksd_admin.ksd_tickets_url);
                            });
                        });
                ;
            };
            /**While working on a single ticket, switch between reply/forward and Add note modes
             * We define the action (used by AJAX) and change the submit button's text
             */
            $('ul.edit-ticket-options li a').click(function (e) {
                e.preventDefault();
                action = $(this).attr("href").replace("#", "");
                switch (action) {
                    case "forward_ticket":
                        submitButtonText = ksd_admin.ksd_labels.tkt_forward;
                        break;
                    case "update_private_note":
                        submitButtonText = ksd_admin.ksd_labels.tkt_update_note;
                        break;
                    default:
                        submitButtonText = ksd_admin.ksd_labels.tkt_reply;
                }
                $("input#ksd-reply-ticket-submit").attr("value", submitButtonText).attr("name",  "ksd_" + action );
            });

            /**For the Reply/Forward/Private Note tabs that appear when viewing a single ticket.*/
            //First check if the element exists
            if ($("ul.edit-ticket-options").length) {
                $("#edit-ticket-tabs").tabs();
            }
        }



        this.newTicket = function () {

            /*On focus, Toggle customer name, email and subject */
            _toggleFieldValues();
            //This mousedown event is very important; without it, the wp_editor value isn't sent by AJAX
            $('form.ksd-new-ticket-admin :submit').mousedown(function () {
                tinyMCE.triggerSave();
            });
            
            /**Validate New Tickets before submitting the form by AJAX**/
            $("form.ksd-new-ticket-admin").validate({
                submitHandler: function (form) {
                    ksdLogNewTicketAdmin(form);
                }
            });
            //Add Attachments
            $('[id^="ksd-add-attachment-"]').click(function () {
                var targetUL = 'ksd_attachments';
                if ($(this).hasClass('ksd_ticket_reply')) {//This is an attachment in single ticket view
                    targetUL = 'ksd_attachments-single-ticket';
                }
                if (this.window === undefined) {
                    this.window = wp.media({
                        title: ksd_admin.ksd_labels.tkt_attach_file,
                        multiple: true,
                        button: {text: ksd_admin.ksd_labels.tkt_attach}
                    });
                    var self = this; // Needed to retrieve our variable in the anonymous function below                    
                    this.window.on('select', function () {
                        var files = self.window.state().get('selection').toArray();
                        $.each(files, function (key, attachmentRaw) {
                            attachment = attachmentRaw.toJSON();
                            attachmentLink = '<a href="' + attachment.url + '">' + attachment.filename + ' <span="ksd-attach-filesize"> ( ' + attachment.filesizeHumanReadable + ' )</span></a>';
                            attachmentFormInputUrl = '<input type="hidden" name="ksd_attachments[url][]" value="' + attachment.url + '" />';
                            attachmentFormInputTitle = '<input type="hidden" name="ksd_attachments[size][]" value="' + attachment.filesizeHumanReadable + '" />';
                            attachmentFormInputSize = '<input type="hidden" name="ksd_attachments[filename][]" value="' + attachment.filename + '" />';
                            attachmentFormInput = attachmentFormInputUrl + attachmentFormInputTitle + attachmentFormInputSize;
                            $('ul#' + targetUL).append('<li>' + attachmentLink + '<span class="ksd-close-dialog"></span>' + attachmentFormInput + '</li>');
                        });
                    });
                }
                this.window.open();
                return false;
            });
            //On clicking close, delete the attachment
            $('#admin-kanzu-support-desk').on('click', '.ksd-close-dialog', function () {
                $(this).parent().remove();
            });
        }//eof:newTicket()



        this.uiTabs = function () {

            /*Switch the active tab depending on what page has been selected*/
            activeTab = 0;
             
            //If we are in tour mode, activate the dashboard
            if (ksd_admin.ksd_tour_pointers.ksd_intro_tour) {
                activeTab = 0;
            }

            /**Change the title onclick of a side navigation tab*/
            $("#tabs .ksd-main-nav li a").click(function () {
                $('.admin-ksd-title h2').html($(this).attr('href').replace("#", "").replace("_", " "));//Remove the hashtag, replace _ with a space
                if ("yes" === ksd_admin.enable_anonymous_tracking) { 
                    //KSDAnalytics.sendPageView($(this).attr('href').replace("#", "ksd-").replace("_", "-"));//Make it match the admin_tab format e.g. ksd-dashboard, ksd-tickets, etc
                }
            });

        };

        /*
         * Changes a ticket's status
         */
        this.changeTicketStatus = function ( tkt_id, tkt_status, singleTicketView ) {
            singleTicketView = ( 'undefined' === typeof singleTicketView ? false : true );
            KSDUtils.showDialog("loading");
            $.post(ksd_admin.ajax_url,
                    {action: 'ksd_change_status',
                        ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                        tkt_id: tkt_id,
                        tkt_status: tkt_status
                    },
            function (response) {
                var respObj = {};
                //To catch cases when the ajax response is not json
                try {
                    //to reduce cost of recalling parse
                    respObj = JSON.parse(response);
                } catch (err) {
                    KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                    return;
                }

                //Check for error in request.
                if ('undefined' !== typeof (respObj.error)) {
                    KSDUtils.showDialog("error", respObj.error.message);
                    return;
                }
                KSDUtils.showDialog("success", respObj);
                if( !singleTicketView ){
                    _this.ksdRefreshTicketsPage();//Refresh the page
                }
            });

        };

        /**
         *  Attach event on send as email check box to show cc field
         */
         this.attachCCFieldEvents = function () {
            $("form.ksd-new-ticket-admin input[name=ksd_tkt_cc]").css({"display":"none"});
            $('a.ksd-new-ticket-cc').click( function(){
                $("form.ksd-new-ticket-admin input[name=ksd_tkt_cc]").css({"display":"block"});
            });
            if ( $( "form.ksd-new-ticket-admin input[name=ksd_send_email]" ).attr("checked") == "checked")
            {
                $('a.ksd-new-ticket-cc').css({"display":"block"});
            }else{
                $('a.ksd-new-ticket-cc').css({"display":"none"});
            }
            
            //Attach event
            $( "form.ksd-new-ticket-admin input[name=ksd_send_email]" ).change(function() {
                if( $(this).attr("checked") == "checked"){
                    $('a.ksd-new-ticket-cc').css({"display":"block"});
                }else{
                    $('a.ksd-new-ticket-cc').css({"display":"none"});
                    $("form.ksd-new-ticket-admin input[name=ksd_tkt_cc]").css({"display":"none"});
                }
            });
            
         };

        /**
         * Attach an event to the items that change ticket status
         */
        this.attachChangeTicketStatusEvents = function () {
            /**AJAX: Send the AJAX request when a new status is chosen**/
            $("#ticket-tabs").on('click', '.ticket-actions ul.status li', function () {
                var tkt_id = $(this).parent().parent().attr("id").replace("tkt_", "");//Get the ticket ID
                var tkt_status = $(this).attr("class");
                _this.changeTicketStatus(tkt_id, tkt_status);
            });

            /**Hide/Show the change ticket options on click of a ticket's 'change status' item**/
            $("#ticket-tabs").on('click', '.ticket-actions a.change_status', function (event) {
                event.preventDefault();//Important otherwise the page skips around
                var tkt_id = $(this).attr('id').replace("tkt_", ""); //Get the ticket ID
                $("#tkt_" + tkt_id + " ul.status").toggleClass("hidden");
                $(this).parent().find(".ksd_agent_list").addClass("hidden");
                $(this).parent().find("ul.severity").addClass("hidden");
            });

            /**In single ticket view, Hide/Show the change status options*/
            if ($("#ksd-single-ticket").length) {
                $(".ksd-top-nav").on('click', 'a.change_status', function (event) {
                    event.preventDefault();//Important otherwise the page skips around
                    $("ul.status").toggleClass("hidden");
                });
                $(".ksd-top-nav ul.status").bind("mouseleave", function () {
                    $(this).addClass('hidden');
                });
                $(".ksd-top-nav").on('click', 'ul.status li', function () {
                    var tkt_id = $.urlParam('ticket');
                    var tkt_status = $(this).attr("class");
                    _this.changeTicketStatus(tkt_id, tkt_status, true );

                });
            }
        };
        this.attachAssignToEvents = function () {
            //---------------------------------------------------------------------------------
            /**Hide/Show the assign to options on click of a ticket's 'Assign To' item**/
            $("#ticket-tabs").on('click', '.ticket-actions a.assign_to', function (event) {
                event.preventDefault();//Important otherwise the page skips around
                //$(".ticket-actions a.change_status'").hide();
                var tkt_id = $(this).parent().attr('id').replace("tkt_", ""); //Get the ticket ID
                $("#tkt_" + tkt_id + " ul.ksd_agent_list").toggleClass("hidden");
                $(this).parent().find(".status").addClass("hidden");
                $(this).parent().find(".severity").addClass("hidden");

            });
            //Re-assign a ticket 
            $("#ticket-tabs").on('click', '.ticket-actions ul.ksd_agent_list li', function () {
                var tkt_id = $(this).parent().parent().attr("id").replace("tkt_", "");//Get the ticket ID
                var assign_assigned_to = $(this).attr("id");
                _this.reassignTicket(tkt_id, assign_assigned_to);
            });
            /**In single ticket view, Hide/Show the agent list when 'Assign to' is clicked*/
            if ($("#ksd-single-ticket").length) {
                $(".ksd-top-nav").on('click', 'a.assign_to', function (event) {
                    event.preventDefault();//Important otherwise the page skips around
                    $("ul.ksd_agent_list").toggleClass("hidden");
                });
                $(".ksd-top-nav ul.ksd_agent_list").bind("mouseleave", function () {
                    $(this).addClass('hidden');
                });
                $(".ksd-top-nav").on('click', 'ul.ksd_agent_list li', function () {
                    var tkt_id = $.urlParam('ticket');
                    var assign_assigned_to = $(this).attr("id");
                    _this.reassignTicket(tkt_id, assign_assigned_to, true );
                });
            }
            ;
        };

        /**
         * Attach events to items used to change ticket read/unread status
         * @returns {undefined}
         */
        this.attachMarkReadUnreadEvents = function () {
            //Mark ticket read 
            $("#ticket-tabs").on('click', '.ticket-actions a.mark_read', function (event) {
                event.preventDefault();
                var tkt_id = $(this).attr('id').replace("tkt_", ""); //Get the ticket ID
                _this.markTicketReadUnread(tkt_id, 1);
            });
            //Mark ticket unread
            $("#ticket-tabs").on('click', '.ticket-actions a.mark_unread', function (event) {
                event.preventDefault();
                var tkt_id = $(this).attr('id').replace("tkt_", ""); //Get the ticket ID
                _this.markTicketReadUnread(tkt_id, 0);
            });
            //In single page view 
            if ($("#ksd-single-ticket").length) {
                $(".ksd-top-nav").on('click', 'a.mark_unread', function (event) {
                    event.preventDefault();//Important otherwise the page skips around
                    var tkt_id = $.urlParam('ticket');
                    _this.markTicketReadUnread(tkt_id, 0, true );
                });
            }
            ;
        };

        /**
         * Attach events to the items used to change ticket severity
         */
        this.attachChangeSeverityEvents = function () {
            //Hide/Show the change severity menu in ticket grid
            $("#ticket-tabs").on('click', '.ticket-actions a.change_severity', function (event) {
                event.preventDefault();//Important otherwise the page skips around
                var tkt_id = $(this).attr('id').replace("tkt_", ""); //Get the ticket ID
                $("#tkt_" + tkt_id + " ul.severity").toggleClass("hidden");
                $(this).parent().find(".ksd_agent_list").addClass("hidden");
                $(this).parent().find("ul.status").addClass("hidden");
            });
            /**AJAX: In ticket grid, change severity on click of a single ticket**/
            $("#ticket-tabs").on('click', '.ticket-actions ul.severity li', function () {
                var tkt_id = $(this).parent().parent().attr("id").replace("tkt_", "");//Get the ticket ID
                var tkt_severity = $(this).attr("class");
                _this.changeTicketSeverity(tkt_id, tkt_severity );
            });

            /**In single ticket view, Hide/Show the severity list when 'Change Severity' is clicked*/
            if ($("#ksd-single-ticket").length) {
                $(".ksd-top-nav").on('click', 'a.change_severity', function (event) {
                    event.preventDefault();//Important otherwise the page skips around
                    $("ul.severity").toggleClass("hidden");
                });
                $(".ksd-top-nav ul.severity").bind("mouseleave", function () {
                    $(this).addClass('hidden');
                });
                $(".ksd-top-nav").on('click', 'ul.severity li', function () {
                    var tkt_id = $.urlParam('ticket');
                    var tkt_severity = $(this).attr("class");
                    _this.changeTicketSeverity(tkt_id, tkt_severity, true );
                });
            }
            ;
        };

        this.uiSingleTicketView = function () {
            //Add a class ksd-ticket to all ticket single views
            if( $.urlParam('post') > 0 && $( '.ksd-misc-customer' ).length ){
                $( '#post-body' ).addClass( 'ksd-ticket-post-body' );
                //Replace the ticket title with an h2 item
                $( '#titlewrap label#title-prompt-text' ).remove();
                $( '#titlewrap' ).html ( '<h2 class="post_title">'+$( '#titlewrap input#title').val() +'</h2>' );
            }

            //Add click event to the reply to all button.   //@TODO Update this                     
            $("#edit-ticket #reply_toall_button").click(function(){
                $("form#edit-ticket input[name=ksd_tkt_cc]").css({"display":"block"});
                $("form#edit-ticket input[name=ksd_tkt_cc]").val( $(this).attr("data"));
            });            
            
            if ( $("#ksd-activity-metabox").hasClass("pending")) {
                _this.getTicketActivity();                
            }; 
            //Modify the submitdiv
            _this.modifySubmitDiv();    
        };//eof:this.uiSingleTicketView
        
        /**
         * Modify the submit div in reply ticket & new ticket modes
         * @returns {undefined}
         */
        this.modifySubmitDiv = function () {
            //Check if this is our (ticket) page
            if( 'ksd_ticket' !== $.urlParam('post_type') && !$('#ksd-messages-metabox').length ) return;
            //Manually move the submitdiv to ensure it is the first element
            $("#submitdiv.postbox").prependTo("#side-sortables.meta-box-sortables");
            //Modify the post status options available
            $( 'select#post_status' ).html( ksd_admin.ksd_ticket_info.status_list );
            //Show the current status. Get it from #hidden_ksd_post_status. We add a class to capitalize the text
            var currentStatus = $( '#hidden_ksd_post_status').val();
            if ( 'auto-draft' === currentStatus ){
                currentStatus = 'open';
            }
            $('#post-status-display').text( currentStatus  ).addClass('ksd-post-status-display').addClass( currentStatus );
            $( 'a.save-post-status' ).click( function( event ) {//On change status, add the new status to the hidden hidden_ksd_post_status field
                event.preventDefault();
                var newStatus =  $('option:selected', $('#post_status') ).val();
                $( '#hidden_ksd_post_status' ).val( newStatus );
                //Replace the classes wrapping the displayed status
                $('#post-status-display').removeClass().addClass('ksd-post-status-display').addClass( newStatus );                
            });
           //Change the 'Save Draft' button text to just 'Save'
           $( '#save-action' ).remove();
           $('#preview-action').remove();
           //Cancel a change 'assign to' or 'severity'
           $( 'a.cancel-severity,a.cancel-assign-to,a.cancel-customer' ).click( function( event ){
               event.preventDefault();
               $(this).parent().addClass('hidden');
           });
           //Edit 'assign to' or 'severity'
           $( 'a.edit-assign-to,a.edit-severity,a.edit-customer' ).click( function( event ){
               event.preventDefault();
               $(this).parent().find('div.ksd_tkt_info_wrapper').removeClass('hidden');
           });
           //Save 'Assign to' or 'severity'
            $( 'a.save-severity,a.save-assign-to,a.save-customer' ).click( function( event ){
               event.preventDefault();
               var theParent = $(this).parent();
               var newValue =  $('option:selected', theParent.find('select') ).text();              
               //Add class for severity.
               if (  theParent.parent().hasClass( 'ksd-misc-severity') ){
                    theParent.parent().find( '.ksd-misc-value' ).text( newValue ).removeClass().addClass('ksd-misc-value').addClass( newValue.toLowerCase() );
               }//Add class for customer
               if (  theParent.parent().hasClass( 'ksd-misc-customer') ){
                    theParent.parent().find( '.ksd-misc-value' ).text( newValue ).removeClass().addClass('ksd-misc-value').addClass( newValue.toLowerCase() );
                }else{
                    theParent.parent().find( '.ksd-misc-value' ).text( newValue );   
               }
               theParent.addClass('hidden');
           });
           //Change the 'Publish' button to 'Update'. Ensure that we are on a KSD ticket page
           if( $.urlParam('post') > 0 && $( '.ksd-misc-customer' ) ){
               $( '#publish' ).val( ksd_admin.ksd_labels.lbl_update );   

               //Update the text even when it changes. Change WP's postL10n.publish variable
                $( '#post-status-select a.save-post-status' ).click( function(){
                    postL10n.publish = ksd_admin.ksd_labels.lbl_update ;   
                });                           
           }


           
           //Hide Visibility for ksd_ticket post types
           $('#submitdiv #visibility').hide();
           
           //Hide Publish Date for ksd_ticket post types
           $('#submitdiv #timestamp').parent().hide();
           //Add a 'Created on' element if we are replying a ticket
           if( $.urlParam('post') > 0 ){
                var  createdOn = '<div class="misc-pub-section curtime misc-pub-curtime" style="">';
                     createdOn+='<span id="timestamp">';
                     createdOn+=ksd_admin.ksd_labels.lbl_created_on+': <b>'+$('#submitdiv #timestamp b').text()+'</b></span>';	
                     createdOn+='</div>';
                $( createdOn ).insertAfter( '.ksd-misc-assign-to' );      
            }
        };

        /**
         * Get a ticket's activity
         * @returns {undefined}
         */
        this.getTicketActivity = function () {
            $.post(ksd_admin.ajax_url,
                    {   action: 'ksd_get_ticket_activity',
                        ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                        ksd_reply_title: $('input[name=post_title]').val(),
                        tkt_id: $.urlParam('post')//We get the ticket ID from the URL
                    },
            function (response) {
                var respObj = {};
                //To catch cases when the ajax response is not json
                try {
                    //to reduce cost of recalling parse
                    respObj = JSON.parse(response);
                } catch (err) {
                    KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                    return;
                }

                //Check for error in request.
                if ('undefined' !== typeof (respObj.error)) {
                    KSDUtils.showDialog("error", respObj.error.message);
                    return;
                }
                if ( ! $.isArray(respObj)) {
                    $("#ksd-activity-metabox").html(respObj);
                    return;
                }
                var ticketActivityData = '<ul>';
                $.each( respObj, function ( key, ticketActivity ) {
                    ticketActivityData+='<li>'+ticketActivity.post_date+' '+ticketActivity.post_author+' '+ticketActivity.post_content+'</li>';
                });
                ticketActivityData+='</ul>';
                $("#ksd-activity-metabox").html(ticketActivityData);
            });
        };

        /**Toggle the form field values for new tickets on click**/
        _toggle_form_field_input = function(event) {
            if ($(this).val() === event.data.old_value) {
                $(this).val(event.data.new_value);
            }
        }

        _toggleFieldValues = function () {
            //The fields
            var new_form_fields = {
                "ksd_tkt_subject": ksd_admin.ksd_labels.tkt_subject,
                "ksd_cust_fullname": ksd_admin.ksd_labels.tkt_cust_fullname,
                "ksd_cust_email": ksd_admin.ksd_labels.tkt_cust_email,
                "ksd_tkt_cc": ksd_admin.ksd_labels.lbl_CC
            };
            //Attach events to the fields  
            $.each(new_form_fields, function (field_name, form_value) {
                $('form.ksd-new-ticket-admin input[name=' + field_name + ']').on('focus', {
                    old_value: form_value,
                    new_value: ""
                }, _toggle_form_field_input);
                $('form.ksd-new-ticket-admin input[name=' + field_name + ']').on('blur', {
                    old_value: "",
                    new_value: form_value
                }, _toggle_form_field_input);
            });
        };

 

        this.attachRefreshTicketsPage = function () {
            $('.ksd-ticket-refresh button').click(function () {
                _this.ksdRefreshTicketsPage( );
            });
        };
 


        _getTabId = function (tab_id) {
            var tab_id_name = "#tickets-tab-" + tab_id;
            return tab_id_name;
        };
        /*Add effects to ticket row
         * Add border to the ksd-row-ctrl table row
         * */
        RowCtrlEffects = function () {

            $(".ksd-row-ctrl").bind("hover mouseover focus", function () {

                var id = $(this).attr("id");
                var tkt_id = $(this).attr("id").replace("ksd_tkt_ctrl_", "");
                $("#ksd_tkt_id_" + tkt_id).addClass("ksd-row-ctrl-hover");


            });

            $(".ksd-row-ctrl").mouseout(function () {
                var id = $(this).attr("id");
                var tkt_id = $(this).attr("id").replace("ksd_tkt_ctrl_", "");
                $("#ksd_tkt_id_" + tkt_id).removeClass("ksd-row-ctrl-hover");
            });


            /*All checkbox**/
            $("#ticket-tabs .tkt_chkbx_all").on("click", function () {
                //TODO:Show all options
                if ($(this).prop('checked') === true) {
                    $("#tkt_all_options").removeClass("ticket-actions");
                    $('input:checkbox').not(this).prop('checked', this.checked);

                    //
                    tab_id = $(this).attr("id").replace("tkt_chkbx_all_", "");
                    $("#ksd_row_all_" + tab_id).removeClass('ksd-row-all-hide').addClass("ksd-row-all-show");


                } else {
                    $("#tkt_all_options").addClass("ticket-actions");
                    $('input:checkbox').not(this).prop('checked', this.checked);

                    tab_id = $(this).attr("id").replace("tkt_chkbx_all_", "");
                    $("#ksd_row_all_" + tab_id).removeClass('ksd-row-all-show').addClass("ksd-row-all-hide");
                }

            });

        };


        /*
         * 
         * @param {type} tab_id
         * @returns {undefined}
         */
        _getCurrentPage = function (tab_id) {
            var curpage = $("#ksd_pagination_" + tab_id + " ul li .current-nav").html();
            //return (KSDUtils.isNumber(curpage)) ? curpage : 1;
            return parseInt(curpage);
        };


        _getPagLimt = function (tab_id) {
            var limit = $("#ksd_pagination_limit_" + tab_id).val();
            return limit;
        };

 

    };

        //Settings
        Settings = new KSDSettings();
        Settings.init();

        //Dashboard
        Dashboard = new KSDDashboard();
        Dashboard.init();

        //Help
        KSDHelpObj = new KSDHelp();
        KSDHelpObj.init();

        //Tickets
        Tickets = new KSDTickets();
        Tickets.init();
 

});
})(jQuery);
