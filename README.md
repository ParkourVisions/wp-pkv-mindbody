WordPress PKV MindBody
======================

The wp-pkv-mindbody WordPress plugin provides integration with the [MindBody](http://mindbodyonline.com) scheduling software used by [Parkour Visions](http://parkourvisions.org). It's designed to fit the needs of the Parkour Visions website, but it may be of some use to other users of MindBody as well.



Features
--------

* Integrates MindBody's authentication with WordPress', allowing WordPress users to log into MindBody with their WordPress account. This can be combined with a plugin like [JanRain Engage](http://wordpress.org/extend/plugins/rpx/) to provide OpenID login to MindBody.

Installation
------------

* Git clone this repository into your wordpress `wp-content/plugins` folder.
* Extract the `wp-pkv-wordpress` folder to your `wp-content/plugins` folder.
* Activate the plugin in your WordPress admin interface.
* Go to your MindBody site using the owner login and find the Issue API Credentials page. Copy the info into the settings. See [this support topic to find the menu item](http://getsatisfaction.com/mindbody/topics/cant_find_the_option_to_issue_api_credentials).

For now, settings should be added to wp-config.php as defines:

    define('PKV_MINDBODY_CRYPTO_KEY', base64_decode('<generated crypto key>'));
    define('PKV_MINDBODY_SOURCE_NAME', <your source name>);
    define('PKV_MINDBODY_PASSWORD', <your API password>);
    define('PKV_MINDBODY_SITE_ID', <your site id>);

You can generate a crypto key with the following PHP code:

    <?php echo base64_encode(openssl_random_pseudo_bytes (32)); ?>


MindBody Login Page
-------------------

The plugin provides functionality to build a login page that will redirect users to MindBody, automatically logging them in (assuming they've saved their MindBody credentials in their user profile). However, you must do a little work to set this up.

* Create a page.
* Open the Custom Fields panel on the page editor. You may have to go to "Screen Options" and enable it.
* Enter "mindbody-login" as the field name, and "true" as the value. Click "Add Custom Field".
* In the post body, type "[mindbody-login]". That's where the login form will appear for people who have not yet saved their MindBody credentials, or who can't be logged in. You can put other content around the form to help explain what's up with the integration or how MindBody is used.
* Link to your page. It's not a bad idea to put the link in a template behind an is_user_logged_in() check to only offer them the option to auto-login to MindBody if they have an account.

Future Ideas
------------

* Widgets - class schedules, your schedule, etc.
* Automatically create MindBody accounts for new users
* Sync MindBody accounts back to WordPress.
