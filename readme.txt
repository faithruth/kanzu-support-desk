=== Kanzu Support Desk - WordPress Helpdesk  Plugin ===
Contributors: kanzucode
Donate link: https://kanzucode.com/
Tags: helpdesk,ticketing,ticket system,customer service,ticket,system,support,help,support system,crm,customer relationship management
Requires at least: 3.0.1
Requires PHP: 5.3
Tested up to: 4.8
Stable tag: 2.4.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Kanzu Support Desk (KSD) is a simple help desk solution that keeps your customer interactions fast & personal.

== Description ==

Great customer care is at the heart of every good product or service. Kanzu Support Desk simplifies the process of tracking customer requests, grouping them, assigning them and giving prompt
feedback, all with a personal touch. KSD fits into your native WordPress interface like a hand in a snug glove so that you can get to do a lot intuitively.

KSD allows you to:

* Use a very simple, native WordPress interface
* Receive email notifications to keep you and your happy customer up-to-date on a ticket's progress
* Work seamlessly with WooCommerce or Easy Digital Downloads
* Have as many agents as you want
* Track changes to a ticket from a ticket log
* Monitor your performance from beautiful graphs on your dashboard
* Create private notes on tickets to allow agents share extra information on tickets with each other
* Re-assign tickets in a jiffy
* Group tickets based on products/categories/tags/departments - whatever works for you
* Change to a language you prefer since the plugin supports translation. You can [contribute to translations here](https://translate.wordpress.org/projects/wp-plugins/kanzu-support-desk)

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'kanzu-support-desk'
3. Click 'Install Now'
4. Activate the plugin in the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `kanzu-support-desk.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `kanzu-support-desk.zip`
2. Extract the `kanzu-support-desk` directory to your computer
3. Upload the `kanzu-support-desk` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

= After activation =
You'll get an intro message and walk through an quick tour of the plugin. You'll create your first ticket and see how to send a reply and make changes to it.

At installation, two pages are created automatically:

1. Submit Ticket    - Has a support form for customers to submit tickets
2. My Tickets       - Shows a customer's tickets after they log in. They'll track ticket progress here

== Support Forms ==
In case you'd like to have extra support forms, use shortcode [ksd_support_form] to create a support form anywhere. Alternatively, use the support form widget
If you'd like customers to be able to use a support form tucked away in the bottom right of every page on your site, enable the "Show support button" option in your KSD settings.

You can also get our [Mail add-on](https://kanzucode.com/downloads/kanzu-support-desk-mail) to allow your customers create tickets by emailing your support email address.

Adding CAPTCHA inputs to a form is a good way to prevent spam. To add CAPTCHA to your support form, go [here](https://www.google.com/recaptcha/admin), get Google reCAPTCHA keys and then add them to your KSD settings.

Please check out [our documentation here](https://kanzucode.com/kb_topic/kb-kanzu-support-desk/) for a more detailed walk-through should you need it

= Follow the action =
Contribute to the dev process on [GitHub](https://github.com/kanzucode/kanzu-support-desk)<br />
Get the latest in KSD and WP in general from [@KanzuCode on Twitter](https://twitter.com/KanzuCode)

== Frequently Asked Questions ==

= Are tickets public? =

By default, tickets are only visible to the customer who logged them and your staff ( users with role ksd_agent, ksd_supervisor or a site administrator)

= Will KSD work with my theme? =

Yes, it will


= Can an agent delete a ticket? =

No. Only supervisors and administrators can delete tickets

== Screenshots ==

1. The dashboard showing ticket volumes
2. Creation of a new ticket from the back-end
3. The settings panel
4. The ticket grid.
5. The details of a single ticket and its corresponding replies
6. Changing a ticket's status
7. Private note support

== Changelog ==
 = 2.4.7, May 20, 2019 =
 * SECURITY FIX | Check whether user's logged in before running custom admin actions

 = 2.4.6, May 06, 2019 =
 * Remove references to add-ons

 = 2.4.5, June 19, 2017 =
 * BUGFIX | Fix undefined variables. Support WP 4.8

 = 2.4.4, May 17, 2017 =
 * BUGFIX | Enable plugin to be uninstalled

 = 2.4.3, May 12, 2017 =
 * BUGFIX | Proper support for HTML emails

 = 2.4.1, May 10, 2017 =
 * Security Fix | Remove vulnerable WP_session

= 2.4.0, May 10, 2017 =
 * Contact form 7 integration
 * BUG FIX | Update  email headers that were forcing all mail to be plain text
 * Add questionnaire
 * Change first customer username
 * Add support tab in contextual help
 * Add feedback menu items

= 2.3.5, March 24, 2017 =
 * BUG FIX | Add missing freemius file

= 2.3.4, March 24, 2017 =
 * Add ability to merge multiple tickets
 * Change default tickets icon
 * Restyle replies to include gravatar and date in new format
 * Load admin replies synchronously
 * BUG FIX | Stop highlighting admin menu item on click
 * Changed 'Reply to all' to just 'Reply'
 * Replace jQuery's deprecated addSelf with addBack
 * Add filters for reply notifications

= 2.3.3, March 3, 2017 =
 * New on-boarding with welcome message in multiple tabs and single initial ticket
 * Freemius Insights integration with opt-in
 * Add new KSD Mail SAAS support
 * Admin menu for discounts, getting started guide and quick tour
 * WooCommerce support tab added
 * 'Edit' in row actions changed to 'Reply'
 * 'Update' and 'Publish' buttons changed to 'Submit'
 * Remove custom meta fields below posts

= 2.3.2, January 23, 2017 =
 * Add Facebook add-on support
 * Run new discount offer

= 2.3.1, November 1, 2016 =
 * Localize status and severity in ticket grid
 * Position ticket error label
 * BUG FIX | Correctly mark ticket as read on reply
 * BUG FIX | Save reCAPTCHA key properly, don't let the xxxxx mess it up

= 2.3.0, October 22, 2016 =
 * Add read/unread ticket indicator
 * Add unread count next to Tickets label
 * Use wp_editor for private notes
 * Add ksd_reply_ticket action hook to add-ons to log replies
 * Add 'Reset role capabilities' button
 * Add 'ksd_ticket_info_customer_email' and 'ksd_registration_form' filters
 * Internationalize ALL strings, including jQuery validation strings

= 2.2.12, October 4, 2016 =
 * Add KSD()->support_form() call
 * Add ksd_my_tickets_array filter
 * BUG FIX | Display widget content
 * Remove KSD from role names

= 2.2.11, September 16, 2016 =
 * BUG FIX | Rename reCAPTCHA callback to eliminate clash with Contact form 7
 * Remove 'enable ticket notifications' setting
 * BUG FIX | Allow saving of enable/disable products
 * Remove deprecated getTickets method
 * Restrict all ticket assignments to ksd_agents,ksd_supervisors & administrators
 * In single ticket view, replace permalink with hashURL for tickets with hash URLs
 * Add 'My Tickets' link to customer personal profile
 * Mask the reCAPTCHA secret key
 * Add filters and actions to support making select tickets public
 * In auto-reply, support {customer_display_name} and use original subject
 * Add Send debug email setting
 * Replace template html-public-new-ticket; use single-submit-ticket instead
 * Re-do onboarding to use keys, not digits and add knowledge base links at the end

= 2.2.10, August 27, 2016 =
 * BUG FIX | Autofix new roles and capabilities for any user who's update didn't do it

= 2.2.9, August 27, 2016 =
 * Add custom KSD roles
 * Restore Analytics
 * Support multiple Google reCAPTCHA forms

= 2.2.8, May 31, 2016 =
 * BUG FIX | Remove lingering UA Analytics
 * Add customer selection while creating a ticket
 * BUG FIX | Shortcode support form doesn't show at the top of the page anymore
 * User request - Add referrer to ticket info

= 2.2.7, May 24, 2016 =
 * Remove UA tracking

= 2.2.6, May 16, 2016 =
 * BUG FIX | Add support for custom fields.

= 2.2.5, May 14, 2016 =
 * Sort tickets by last time updated
 * Add support for KSD Custom fields add-on

= 2.2.4, May 8, 2016 =
 * Submission of attachments in front-end support form added
 * BUG FIX | Remove second CC button from the toolbar of the editor during ticket reply
 * Display customer information in separate metabox; add display pic
 * Display Woo and EDD customers in customer list
 * Allow editing of ticket customer
 * Minor color changes to single ticket view UI

= 2.2.3, April 15, 2016 =
 * BUG FIX | Wrong ticket details shown in single ticket view when other tickets exist
 * User request - Add filter for customer html in ticket information
 * BUG FIX | Only replace statuses on ticket list page
 * BUG FIX | In support form, don't clear ticket message on focus

= 2.2.2, April 3, 2016 =
 * Update translation strings

= 2.2.1, March 27, 2016 =
 * Disable KSD Customer redirection on login
 * BUG FIX | Preformatted text shouldn't overflow in replies

= 2.2.0, March 22, 2016 =
 * WooCommerce and EDD support added
 * Onboarding process added to guide through first ticket creation
 * Feedback mechanism added
 * Notifications sent on ticket re-assignment
 * BUG FIX | Quick editing title would reset post
 * BUG FIX | KSD Rules settings reset on update

= 2.1.2, March 09, 2016 =
 * BUG FIX | Single ticket public replies failing
 * BUG FIX | Showing loading button before send action

= 2.1.1, February 29, 2016 =
 * Added ksd_settings_updated js "action" hook
 * BUG FIX | Settings only showing for the first addon when more than one addon is enabled

= 2.1.0, February 21, 2016 =
 * Change settings UI to horizontal tabs
 * Allow guests to create tickets. Hash URLs created for this
 * Products, severity and category added to support form
 * Support for add-on settings as an array
 * Re-direct to 'My tickets', not 'Submit Ticket' on KSD customer login
 * Change support form hidden tab to bottom right
 * In settings, add descriptions in addition to tooltips
 * BUG FIX | Multiple support forms supported on the same page

= 2.0.9, January 27, 2016 =
 * BUG FIX | Failure to send front-end replies due to tinyMCE not loading
 * Re-order ticket replies in ascending order
 * BUG FIX | 404 error on single ticket pages

= 2.0.8, January 26, 2016 =
 * BUG FIX | 'Enable customer registration' saves correctly
 * BUG FIX | Support form not showing in bootstrap themes

= 2.0.7, January 20, 2016 =
 * Add support form widget

= 2.0.6, December 09, 2015 =
 * BUG FIX | New ticket notifications going to primary admin instead of auto-assign user

- 2.0.6, October 05, 2015 =
 * BUG FIX | Large images in replies extending before the reply area

- 2.0.5, September 30, 2015 =
 * BUG FIX | Updating settings not working in Safari
 * BUG FIX | "- Draft" declaration removed from all post types when plugin is installed

= 2.0.4, September 19, 2015 =
 * Support CCs in tickets
 * BUG FIX | Do add-on updates seamlessly

= 2.0.3, September 05, 2015 =
 * BUG FIX | Generate debug file correctly

= 2.0.2, September 05, 2015 =
 * BUG FIX | Agent could not send a reply
 * Add reply count to ticket grid

= 2.0.1, September 03, 2015 =
 * Correct 'Settings' link in plugins screen
 * Remove logic for KSD < 1.5.0
 * Fix access permissions: Unauthenticated user shouldn't see ticket(s)

= 2.0.0, August 29, 2015 =
 * Overhaul: Switched from custom tables to custom post types for all ticket info
 * Customers can reply and follow ticket progress from your website
 * Customers required to create accounts before submitting tickets

= 1.7.0, July 29, 2015 =
 * Better notifications on new tickets/replies
 * Ticket cc feature added
 * Internationalized validation error messages
 * Added KSD customer buttons to settings
 * Added 'Generate debug file' to settings
 * Ticket & Replies formatted for display and email sending. Better HTML support
 * Auto-reply HTML support added
 * Optional notification email
 * KSD now has a Logo

= 1.6.8, July 17, 2015 =
 * BUG FIX | Send notifications on new ticket logged
 * Mail reply wrap updated

= 1.6.7, June 30, 2015 =
 * Intro tour updated. Adds intro message from CEO
 * Tracking message updated to show usage & error stats

= 1.6.6, June 27, 2015 =
 * BUG FIX | Save plugin license info correctly

= 1.6.5, June 26, 2015 =
 * BUG FIX | Plugin activation/deactivation fixed

= 1.6.4, June 25, 2015 =
 * Make support button text configurable
 * BUG FIX | Allow Google Analytics disabling/activation
 * BUG FIX | Track only KSD pages
 * Support HTML email replies
 * Highlight add-on submenu, populate with extra add-ons

= 1.6.3, June 24, 2015 =
 * BUG FIX | Show refresh message when nonce expires
 * Plugin updates handled by KSD plugin

= 1.6.2, June 10, 2015 =
 * Mark tickets as read/unread
 * Sort tickets by last time updated
 * Add customer email in single ticket view

= 1.6.1, June 01, 2015 =
 * BUG FIX | Ticket logging without Google reCAPTCHA fixed. Cost us all growth thus far

= 1.6.0, May 27, 2015 =
 * Added attachments to tickets & replies
 * Bulk update options (change status, severity, re-assign, delete ) added
 * HTML replies supported
 * Internationalization of single ticket view options
 * Ticket grid default list increased to 20 from 5

= 1.5.5, May 16, 2015 =
 * Change of ticket status colors to more intuitive ones
 * Addition of pre-ticket logging filter
 * Support for the KSD Rules add-on

= 1.5.4, April 15, 2015 =
 * Ticket importation from CSV files added
 * Renamed show support tab and updated explanation
 * Notify primary admin on new ticket creation
 * Internalization strings updated to use single quotes

= 1.5.3, March 21, 2015 =
 * BUG FIX | Better support for localization
 * Customers table no longer created at installation

= 1.5.2, February 25, 2015 =
 * BUG FIX | Get correct role-based agent list in single ticket view

= 1.5.1, February 24, 2015 =
 * BUG FIX | Added missing icons (more_top,ellipsis), updated loading_dialog.GIF to loading_dialog.gif

= 1.5.0, February 24, 2015 =
 * Added auto-assign feature for new tickets
 * Migrated customers from KSD customers table to wp_users
 * Role-based ticket management added

= 1.4.0, February 10, 2015 =
 * Added Analytics
 * Added sweet notifications panel
 * Added client-side validation for Google reCAPTCHA
 * Introductory tour updated to be more user-friendly
 * Fixed typo. occured updated to occurred
 * Documentation links updated

= 1.3.1, February 05, 2015 =
 * CAPTCHA added to front-end form

= 1.3.0, January 31, 2015 =
* BUG FIX | Saving checkbox settings corrected
* [ksd_support_form] shortcode added!
* Edit ticket options (Change status, severity, owner) in single ticket view

= 1.2.1, January 26, 2015 =
* BUG FIX | Save messages & replies containing apostrophes properly
* Style single ticket view, delete dialog
* Update documentation URLs

= 1.2.0, January 24, 2015 =
* Default tickets pre-populated on installation
* In tickets, show total number of tickets in each ticket filter
* Severity and status indicators added
* BUG FIX | Sanitization of ticket message and replies now done to allow HTML content
* 'NEW' ticket status added, 'ASSIGNED' removed from available ticket status options
* In tickets, show 'Loading' dialog on initial load and on filter selection
* 'New Ticket' tab re-arranged for easier use
* Dashboard summary statistics re-styled and made clickable
* Dashboard graph date format changed to DD-MM-YYYY
* Ticket grid re-styled to highlight ticket subject & OPEN tickets
* On the ticket grid, added number of replies per ticket

= 1.1.3 =
* BUG FIX | Eliminated subject/message length error returned for tickets not logged by add-ons

= 1.1.2 =
* BUG FIX | Removed JSON_NUMERIC_CHECK which is only supported in PHP >=5.3
* BUG FIX | Dashboard graph wasn't being generated on sites with SSL (HTTPS)

= 1.1.1 =
* BUG FIX | MySQL <=5.5 tables weren't being created
* Proper styling for the settings view
* Gracefully handle errors in dashboard AJAX response

= 1.1.0 =
* Introductory tour on activation
* 1/12/14 Tickets logged by an action
* Feedback form added to help tab
* Newsletter opt-in added
* Add-on list retrieved from KSD add-on feed

= 1.0.0, November 21, 2014 =
* Launched.

== Upgrade Notice ==
= 2.4.5 =
 * BUGFIX | Fix undefined variables. Support WP 4.8

= 2.4.4 =
 * BUGFIX | Enable plugin to be uninstalled

= 2.4.3 =
 * BUGFIX | Proper support for HTML emails

= 2.4.1 =
 * Security Fix | Remove vulnerable WP_session

= 2.4.0 =
 * Contact form 7 support
 * BUG FIX | Update  email headers that were forcing all mail to be plain text
 * Add questionnaire
 * Change first customer username
 * Add support tab in contextual help

= 2.3.5 =
 * Add missing freemius files

= 2.3.4 =
 * Add ability to merge multiple tickets
 * Change default tickets icon
 * Restyle replies to include gravatar and date in new format

= 2.3.3 =
 * Admin menu for discounts, getting started guide and quick tour
 * WooCommerce support tab added
 * 'Edit' in row actions changed to 'Reply'
 * 'Update' and 'Publish' buttons changed to 'Submit'
 * Remove custom meta fields below posts

= 2.3.2 =
 * Add Facebook add-on support
 * Run new discount offer

= 2.3.1 =
 * BUG FIX | Correctly mark ticket as read on reply
 * BUG FIX | Save reCAPTCHA key properly, don't let the xxxxx mess it up

= 2.3.0 =
 * Add read/unread ticket indicator
 * Add unread count next to Tickets label
 * Use wp_editor for private notes

= 2.2.12 =
 * Use KSD()->support_form() call
 * Use ksd_my_tickets_array filter
 * BUG FIX | Display widget content
 * Remove KSD from role names
= 2.2.12, October 07, 2016 =
 * Add ksd_reply_ticket action hook to addons to log replies

= 2.2.9 =
 * Add custom KSD roles

= 2.2.8 =
 * BUG FIX | Remove lingering UA Analytics
 * Add customer selection while creating a ticket

= 2.2.7 =
 * Remove UA tracking

= 2.2.6 =
 * BUG FIX | Add support for custom fields.

= 2.2.5 =
 * Support for custom fields in the front-end support forms.
 * Sort tickets by last time updated
 * Add support for KSD Custom fields add-on

= 2.2.4 =
 * Submission of attachments in front-end support form added
 * BUG FIX | Remove second CC button from the toolbar of the editor during ticket reply

= 2.2.3 =
 * BUG FIX | Wrong ticket details shown in single ticket view when other tickets exist
 * User request - Add filter for customer html in ticket information

= 2.2.2 =
 * Update translation strings

= 2.2.1 =
 * Disable KSD Customer redirection on login
 * BUG FIX | Preformatted text shouldn't overflow in replies

= 2.2.0 =
 * WooCommerce and EDD support added
 * Onboarding process added to guide through first ticket creation
 * Feedback mechanism added

= 2.1.2 =
 * BUG FIX | Single ticket public replies failing
 * BUG FIX | Showing loading button before send action

= 2.1.1 =
 * Added ksd_settings_updated js "action" hook
 * BUG FIX | Settings only showing for the first addon when more than one addon is enabled

= 2.1.0 =
 * Change settings UI to horizontal tabs
 * Allow guests to create tickets. Hash URLs created for this
 * Products, severity and category added to support form

= 2.0.9 =
 * BUG FIX | Failure to send front-end replies due to tinyMCE not loading
 * Re-order ticket replies in ascending order
 * BUG FIX | Fix 404 error on single ticket pages

= 2.0.8 =
 * BUG FIX | 'Enable customer registration' saves correctly
 * BUG FIX | Support form not showing in bootstrap themes

= 2.0.7 =
 * Added support form widget

= 2.0.6 =
 * BUG FIX | New ticket notifications going to primary admin instead of auto-assign user

= 2.0.5 =
 * BUG FIX | Update button fix for Safari browser

= 2.0.4 =
 * Support for cc's in tickets

= 2.0.3 =
 * BUG FIX | Generate debug file correctly

= 2.0.2 =
 * BUG FIX | Agent could not send a reply
 * Add reply count to ticket grid

= 2.0.1 =
 * Correct 'Settings' link in plugins screen
 * Remove logic for KSD < 1.5.0

= 2.0.0 =
 * Customers can view and follow ticket progress from your website

= 1.6.8 =
 * BUG FIX | Notification on new tickets. Mail ticket reply wrapping improved

= 1.6.7 =
 * Intro message updated, tracking label and message also updated

= 1.6.6 =
 * BUG FIX | Save plugin license information correctly

= 1.6.5 =
 * BUG FIX | Plugin activation/deactivation fixed

= 1.6.3 =
 * Support button text made configurable. Bug fixes & add-on list

= 1.6.3 =
 * BUG FIX | Show refresh message when nonce expires

= 1.6.2 =
 * Mark tickets as read/unread

= 1.6.1 =
 * Ticket logging without Google reCAPTCHA fixed

= 1.6.0 =
 * Attachments now supported. Bulk changes to tickets supported

= 1.5.5 =
 * Change of ticket status colors to more intuitive ones

= 1.5.4 =
 * Ticket importation from CSV added, notify primary admin on new ticket creation

= 1.5.3 =
 * BUG FIX | Better support for localization

= 1.5.2 =
 * BUG FIX | Get correct role-based agent list in single ticket view

= 1.5.1 =
 * BUG FIX | Added missing icons (more_top,ellipsis), updated loading_dialog.GIF to loading_dialog.gif

= 1.5.0 =
 * Auto-assign feature for new tickets, role-based ticket management

= 1.4.0 =
* New Notifications panel to keep you updated & client-side validation for Google reCAPTCHA

= 1.3.1 =
* CAPTCHA form added to front-end form

= 1.3.0 =
* [ksd_support_tab] short code added,single ticket view ticket edits added

= 1.2.1 =
* BUG FIX | Save messages & replies containing apostrophes properly, style single ticket & delete dialog

= 1.2.0 =
* Ticket grid re-styled to be prettier and more intuitive, dashboard summary statistics bolder & clickable

= 1.1.3 =
* BUG Fix - Eliminated message/subject length error on logging new tickets

= 1.1.2 =
* Support for PHP < 5.3 added, support for graphs on sites with SSL (HTTPS)

= 1.1.1 =
* Create KSD tables, gracefully handle errors in dashboard AJAX response & better styling for settings

= 1.1.0 =
* Feedback options added, optional add-ons updated, intro tour on activation

= 1.0.0 =
* Join the Kanzu Support club
