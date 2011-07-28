<?php
/*
Copyright (C) 2011 Parkour Visions

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/*
Plugin Name: WP PKV MindBody
Plugin URI: http://github.com/ParkourVisions/wp-pkv-mindbody
Description: Provide login integration and other hooks to the MindBody scheduling software.
Version: 1.0.0
Author: Parkour Visions
Author URI: http://parkourvisions.org
License: MIT

Written by Ben Hollis <ben@benhollis.net> for Parkour Visions.
*/

/**
 * Hook run at init, used to set up the pkv-mindbody plugin on each request.
 * See http://codex.wordpress.org/Plugin_API/Action_Reference/init
 */
function pkv_mindbody_init() {
  // if configured...
  if (PKV_MINDBODY_PASSWORD && PKV_MINDBODY_SOURCE_NAME && PKV_MINDBODY_SITE_ID && PKV_MINDBODY_CRYPTO_KEY) {
    // Add info to the user profile page (echoed directly into the page)
    add_action('show_user_profile', 'pkv_mindbody_edit_user_profile');

    // And save that info when the profile is updated
    add_action('personal_options_update', 'pkv_mindbody_update_user_profile');

    // Add a hook when getting the header that will do the mindbody redirect if it's the right page
    add_action('get_header', 'pkv_mindbody_redirect_page');

    // Add a shortcode for the login page to display the mindbody login form if we can't redirect
    // See http://codex.wordpress.org/Shortcode_API
    add_shortcode('mindbody-login', 'pkv_mindbody_login_form' );
  }

  // add an admin panel for setting up API info

  // TODO: maybe add user metadata to current_user object?
  // TODO: do that is_configured thing RPX does to disable functionality unless things are configured
}

add_action('init', 'pkv_mindbody_init');

/**
 * Encrypts plain text (mindbody username or password) for storing in
 * the WP metadata hash securely. Uses the key defined in
 * PKV_MINDBODY_CRYPTO_KEY. $iv is an initialization vector that needs
 * to be the same on encrypt and decrypt.
 */
function pkv_mindbody_encrypt($plain_text, $iv) {
  // note - I think this makes usernames/passwords max out at 32 chars
  return openssl_encrypt($plain_text, 'AES256', PKV_MINDBODY_CRYPTO_KEY, $raw_output = false, $iv); 
}

/**
 * Encrypts plain text (mindbody username or password) for storing in
 * the WP metadata hash securely. Uses the key defined in
 * PKV_MINDBODY_CRYPTO_KEY. $iv is an initialization vector that needs
 * to be the same on encrypt and decrypt.
 */
function pkv_mindbody_decrypt($encrypted_text, $iv) {
  return openssl_decrypt($encrypted_text, 'AES256', PKV_MINDBODY_CRYPTO_KEY, $raw_input = false, $iv); 
}

/**
 * Generate an initioalization vector for use in encrypting/decrypting
 * username and password. This should be saved per user.
 */
function pkv_mindbody_generate_iv() {
  return openssl_random_pseudo_bytes(16);
}

/*
 * Get the encryption initialization vector saved for a particular user.
 */
function pkv_mindbody_retrieve_iv($user_ID) {
  $iv = get_user_meta($user_ID, 'mindbody_crypto_iv', true);
  return base64_decode(get_user_meta($user_ID, 'mindbody_crypto_iv', true));
}

/**
 * Render form fields for your MindBody login credentials, in the
 * user profile page. Called from the show_user_profile action.
 * See http://codex.wordpress.org/Plugin_API/Action_Reference/show_user_profile
 */ 
function pkv_mindbody_edit_user_profile() {
  // WordPress makes a bunch of global variables available, including the current user_ID.
  // See http://ifacethoughts.net/2006/02/25/wordpress-global-variables/ for a whole list.
  global $user_ID;

  $iv = pkv_mindbody_retrieve_iv($user_ID);
  $mindbody_username = pkv_mindbody_decrypt(get_user_meta($user_ID, 'mindbody_username', true), $iv);
  // don't decrypt, just want to see if it's empty
  $mindbody_password_value = get_user_meta($user_ID, 'mindbody_password', true);

  // We put a fake password in the form instead of the real one, so that you can't just look at it
  $mindbody_password = "pkvmindbodyplaceholder";
  if (empty($mindbody_password_value)) {
    $mindbody_password = "";
  }
?>
  <h3>MindBody Credentials</h3>
  <p>MindBody is the system we use to manage gym reservations. Link your
  MindBody account here to enable automatic logins and other features.</p>
  
  <table class="form-table">
    <tr>
      <th>
        <label for="mindbody_username">MindBody Username</label>
      </th>
      <td>
        <input id="mindbody_username" class="regular-text" type="text"
               value="<?php echo $mindbody_username ?>" name="mindbody_username">
        <span class="description">This is the username you use to log into the
        class scheduling system.</span>
      </td>
    </tr>
    <tr>
      <th>
        <label for="mindbody_password">MindBody Password</label>
      </th>
      <td>
        <input id="mindbody_password" type="password" value="<?php echo $mindbody_password ?>"
               name="mindbody_password">
        <span class="description">This is the password you use to log into the
        class scheduling system.</span>
      </td>
    </tr>
  </table>
<?php
}

/**
 * Save values passed in from the user profile page (specifically,
 * the form fields we generated in pkv_mindbody_edit_user_profile.
 * Called from the personal_options_update action.
 * See http://codex.wordpress.org/Plugin_API/Action_Reference/personal_options_update
 */
function pkv_mindbody_update_user_profile() {
  global $user_ID;

  $form_mindbody_username = $_POST["mindbody_username"];
  $form_mindbody_password = $_POST["mindbody_password"];

  // Use a placeholder to handle the case where the password hasn't been changed
  if ($form_mindbody_password == 'pkvmindbodyplaceholder') {
    $iv = pkv_mindbody_retrieve_iv($user_ID);
    $original_mindbody_password = pkv_mindbody_decrypt(get_user_meta($user_ID, 'mindbody_password', true), $iv);
    $form_mindbody_password = $original_mindbody_password;
  }

  $iv = pkv_mindbody_generate_iv();

  if (!empty($form_mindbody_password)) {
    $encrypted_password = pkv_mindbody_encrypt($form_mindbody_password, $iv);
    update_user_meta($user_ID, "mindbody_password", $encrypted_password);    
  } else {
    delete_user_meta($user_ID, "mindbody_password");
  }

  if (!empty($form_mindbody_username)) {
    $encrypted_username = pkv_mindbody_encrypt($form_mindbody_username, $iv);
    update_user_meta($user_ID, "mindbody_username", $encrypted_username);    
  } else {
    delete_user_meta($user_ID, "mindbody_username");
  }

  update_user_meta($user_ID, "mindbody_crypto_iv", base64_encode($iv));
}

/**
 * The login-redirect page is marked with a special custom attribute.
 * This function hooks the get_header action, where it will have the
 * option to try a redirect. If the user doesn't have MindBody credentials
 * in their profile, this will redirect them to their user profile.
 * See http://codex.wordpress.org/Plugin_API/Action_Reference/get_header
 */
function pkv_mindbody_redirect_page() {
  global $post;
  global $pkv_error;

  // If they've submitted the login form, save their credentials. This might not be the right action.
  // If this succeeds, it should just go right on to the redirect.
  if (is_user_logged_in() && $_POST['pkv_mindbody_action'] == 'save' && wp_verify_nonce($_POST['_pkv_mindbody_nonce'], 'pkv_mindbody_save')) {
      pkv_mindbody_update_user_profile();
  }
  
  if (is_page() || is_object($post)) {

    // The login page is identified by the presence of the 'mindbody-login' Custom Field
    if (get_post_meta($post->ID, 'mindbody-login', true)) {

      if (is_user_logged_in() === true) {

        global $user_ID;  

        // load user info
        $iv = pkv_mindbody_retrieve_iv($user_ID);
        $mindbody_username = pkv_mindbody_decrypt(get_user_meta($user_ID, 'mindbody_username', true), $iv);
        $mindbody_password = pkv_mindbody_decrypt(get_user_meta($user_ID, 'mindbody_password', true), $iv);

        // No username/password set! Forget it.
        if (empty($mindbody_username) || empty($mindbody_password)) {
          return;
        }

        require_once(dirname(__FILE__) . '/mindbody-api/clientService.php');

        $sourcename = PKV_MINDBODY_SOURCE_NAME;
        $password = PKV_MINDBODY_PASSWORD;
        $siteID = PKV_MINDBODY_SITE_ID;

        // initialize default credentials
        $creds = new SourceCredentials($sourcename, $password, array($siteID));

        $clientService = new MBClientService();
        $clientService->SetDefaultCredentials($creds);

        // TODO: need some exception handling here
        $result = $clientService->ValidateLogin($mindbody_username, $mindbody_password);

        if ($result == NULL || $result->ValidateLoginResult->Status == "InvalidParameters") {
          $pkv_error = "The MindBody login information we have for you isn't correct. Please re-enter it.";

          // Clear their username and password
          delete_user_meta($user_ID, 'mindbody_username');
          delete_user_meta($user_ID, 'mindbody_password');

          return;
        }
        
        $guid = $result->ValidateLoginResult->GUID;

        // Redirect to MindBody, pre-authenticated via the GUID
        $mb_url = "https://clients.mindbodyonline.com/ASP/ws.asp?studioid=" . $siteID . "&guid=" . $guid;
        header('Location: ' . $mb_url);
      } else {
        // We'll show a log-in form
        $pkv_error = 'Not logged in';
      }
    }
  }
}


/**
 * This outputs the form that lets people associate their MindBody info
 * with their account if the automatic login has failed (or their username
 * and password wasn't correct). It is placed in a page with the [mindbody-login]
 * shortcode.
 */
function pkv_mindbody_login_form() {
  global $pkv_error;

  // This function needs to return a string instead of appending directly to the output
  // so we turn on output buffering and grab the contents using ob_get_contents
  ob_start();

  // Display an error if there was one
  if (isset($pkv_error) && !empty($pkv_error)) {
    // If they're not logged in, show the wordpress login formx
    if ($pkv_error == 'Not logged in') {
        rpx_login_form();
        $output = ob_get_contents();
        ob_end_clean();
        $output = str_replace('Or log in with', 'Log in with your existing account first', $output);
        return $output;
    }

    echo "<div style=\"color:red\" class=\"mindbody-error\">" . $pkv_error . "</div>";
  }

  // Otherwise, show the mindbody credentials form.
  // TODO: Show a mindbody registration option
?>

<form method="post" action="<?php echo get_permalink() ?>" id="loginform" name="loginform">
	<p>
		<label>MindBody Username<br>
		<input type="text" tabindex="10" size="20" value="" class="input" id="mindbody_username" name="mindbody_username"></label>
	</p>
	<p>
		<label>MindBody Password<br>
		<input type="password" tabindex="20" size="20" value="" class="input" id="mindbody_password" name="mindbody_password"></label>
	</p>
	<p class="submit">
		<input type="submit" tabindex="100" value="Save Mindbody Info" class="button-primary" id="wp-submit" name="wp-submit">
    <input type="hidden" name="pkv_mindbody_action" value="save">
    <?php wp_nonce_field('pkv_mindbody_save','_pkv_mindbody_nonce'); ?>
	</p>
</form>
<?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
?>
